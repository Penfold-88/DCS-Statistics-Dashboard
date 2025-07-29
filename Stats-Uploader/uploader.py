import os
import time
import ftplib
import configparser
import logging
from datetime import datetime
from dotenv import load_dotenv
from logging.handlers import RotatingFileHandler

# Load environment variables from .env file
load_dotenv()

CONFIG_FILE = 'config.ini'
FILELIST_FILE = 'filelist.txt'
MAX_RETRIES = 10
RETRY_INTERVAL = 120  # seconds
CHUNK_SIZE = 8192  # 8 KB

# Set up secure logging with rotation
def setup_logging():
    logger = logging.getLogger('uploader')
    logger.setLevel(logging.INFO)
    
    # Remove existing handlers to avoid duplicates
    for handler in logger.handlers[:]:
        logger.removeHandler(handler)
    
    # Create rotating file handler (10MB max, keep 5 backup files)
    handler = RotatingFileHandler('upload.log', maxBytes=10*1024*1024, backupCount=5)
    formatter = logging.Formatter('[%(asctime)s] %(levelname)s: %(message)s')
    handler.setFormatter(formatter)
    logger.addHandler(handler)
    
    # Also log to console
    console_handler = logging.StreamHandler()
    console_handler.setFormatter(formatter)
    logger.addHandler(console_handler)
    
    return logger

# Initialize logger
logger = setup_logging()

def log_message(message, level='info'):
    """Log message with specified level (info, warning, error)"""
    if level == 'error':
        logger.error(message)
    elif level == 'warning':
        logger.warning(message)
    else:
        logger.info(message)

def load_config():
    config = configparser.ConfigParser()
    config.read(CONFIG_FILE)
    
    # Override with environment variables if they exist
    if 'FTP' not in config:
        config.add_section('FTP')
    if 'Paths' not in config:
        config.add_section('Paths')
    if 'Upload' not in config:
        config.add_section('Upload')
    
    # FTP settings from environment variables (takes precedence)
    if os.getenv('FTP_HOST'):
        config.set('FTP', 'host', os.getenv('FTP_HOST'))
    if os.getenv('FTP_USER'):
        config.set('FTP', 'user', os.getenv('FTP_USER'))
    if os.getenv('FTP_PASSWORD'):
        config.set('FTP', 'password', os.getenv('FTP_PASSWORD'))
    if os.getenv('FTP_SECURE'):
        config.set('FTP', 'secure', os.getenv('FTP_SECURE'))
    
    # Path settings from environment variables
    if os.getenv('LOCAL_FOLDER'):
        config.set('Paths', 'local_folder', os.getenv('LOCAL_FOLDER'))
    if os.getenv('REMOTE_FOLDER'):
        config.set('Paths', 'remote_folder', os.getenv('REMOTE_FOLDER'))
    
    # Upload settings from environment variables
    if os.getenv('THROTTLE_SECONDS'):
        config.set('Upload', 'throttle_seconds', os.getenv('THROTTLE_SECONDS'))
    if os.getenv('DISPLAY_COUNTDOWN'):
        config.set('Upload', 'display_countdown', os.getenv('DISPLAY_COUNTDOWN'))
    
    return config

def read_file_list(enabled_files):
    with open(FILELIST_FILE, 'r') as f:
        return [line.strip() for line in f if enabled_files.get(line.strip(), 'false').lower() == 'true']

def upload_file_with_progress(ftp, local_path, remote_path):
    total_size = os.path.getsize(local_path)
    uploaded = 0

    def handle(block):
        nonlocal uploaded
        uploaded += len(block)
        percent = (uploaded / total_size) * 100
        print(f"Uploading {remote_path}: {percent:.2f}% complete", end='\r')

    with open(local_path, 'rb') as f:
        ftp.storbinary(f'STOR {remote_path}', f, blocksize=CHUNK_SIZE, callback=handle)
    log_message(f"Uploading {remote_path}: 100.00% complete")

def connect_ftp(config):
    use_secure = config['FTP'].getboolean('secure', True)
    
    if use_secure:
        try:
            # Try secure FTP first (FTPS)
            ftp = ftplib.FTP_TLS(config['FTP']['host'])
            ftp.auth()  # Set up secure control connection
            ftp.login(config['FTP']['user'], config['FTP']['password'])
            ftp.prot_p()  # Set up secure data connection
            log_message("Connected using secure FTP (FTPS)")
        except Exception as e:
            log_message(f"Secure FTP failed, falling back to plain FTP: {e}", 'warning')
            # Fallback to plain FTP for backward compatibility
            ftp = ftplib.FTP(config['FTP']['host'])
            ftp.login(config['FTP']['user'], config['FTP']['password'])
            log_message("Connected using plain FTP (fallback)", 'warning')
    else:
        # Use plain FTP when explicitly configured
        ftp = ftplib.FTP(config['FTP']['host'])
        ftp.login(config['FTP']['user'], config['FTP']['password'])
        log_message("Connected using plain FTP (configured)")
    
    # Ensure the data directory exists
    remote_folder = config['Paths']['remote_folder']
    try:
        ftp.cwd(remote_folder)
    except ftplib.error_perm:
        # Directory doesn't exist, try to create it
        try:
            ftp.mkd(remote_folder)
            ftp.cwd(remote_folder)
            log_message(f"Created remote directory: {remote_folder}")
        except ftplib.error_perm as e:
            log_message(f"Failed to create remote directory {remote_folder}: {e}")
            raise
    
    return ftp

def try_upload(file, local_folder, config):
    local_path = os.path.join(local_folder, file)
    for attempt in range(1, MAX_RETRIES + 1):
        ftp = None
        try:
            ftp = connect_ftp(config)
            ftp.nlst()
            upload_file_with_progress(ftp, local_path, file)
            log_message(f"Upload successful: {file}")
            return True
        except Exception as e:
            log_message(f"Upload failed (Attempt {attempt}/{MAX_RETRIES}): {file}, Error: {e}", 'warning')
            time.sleep(RETRY_INTERVAL)
        finally:
            if ftp:
                try:
                    ftp.quit()
                except Exception:
                    pass
                try:
                    ftp.close()
                except Exception:
                    pass
    return False

def main():
    config = load_config()
    display_countdown = config['Upload'].getboolean('display_countdown', True)
    log_message("Sky Pirates Stats Uploader started.")
    while True:
        local_folder = config['Paths']['local_folder']
        throttle = int(config['Upload'].get('throttle_seconds', 1))
        enabled_files = config['Files']
        files_to_upload = read_file_list(enabled_files)

        for file in files_to_upload:
            try_upload(file, local_folder, config)
            time.sleep(throttle)

        if display_countdown:
            for remaining in range(3600, 0, -1):
                mins, secs = divmod(remaining, 60)
                print(f"Next upload in: {mins:02d}:{secs:02d}", end='\r')
                time.sleep(1)
            print()
        else:
            time.sleep(3600)

if __name__ == '__main__':
    main()
