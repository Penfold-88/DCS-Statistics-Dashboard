#!/bin/bash

# DCS Statistics Docker Manager - Shell Script Version
# This script manages Docker containers for DCS Statistics
# Supports: pre-flight, start, stop, restart, status, logs, destroy

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Default configuration
DEFAULT_PORT=9080
CONTAINER_NAME="dcs-statistics"
ENV_FILE=".env"

# Function to print colored output
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[OK]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if a port is available
check_port() {
    local port=$1
    if command -v lsof >/dev/null 2>&1; then
        # Linux with lsof
        lsof -i :$port >/dev/null 2>&1 && return 1 || return 0
    elif command -v ss >/dev/null 2>&1; then
        # Linux with ss
        ss -tuln | grep -q ":$port " && return 1 || return 0
    elif command -v netstat >/dev/null 2>&1; then
        # Windows/Linux with netstat
        netstat -an | grep -q ":$port.*LISTEN" && return 1 || return 0
    else
        # Fallback: try to bind to the port
        (echo >/dev/tcp/localhost/$port) >/dev/null 2>&1 && return 1 || return 0
    fi
}

# Function to find an available port
find_available_port() {
    local start_port=$1
    local max_attempts=100
    local port=$start_port
    
    for i in $(seq 1 $max_attempts); do
        if check_port $port; then
            echo $port
            return 0
        fi
        port=$((port + 1))
    done
    
    return 1
}

# Function to get current port from .env file
get_current_port() {
    if [ -f "$ENV_FILE" ]; then
        grep "^WEB_PORT=" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2 || echo "$DEFAULT_PORT"
    else
        # .env file does not exist
        if [ -f ".env.example" ]; then
            print_warning "No .env file? Bold choice..."
            echo -e "BTW, ${CYAN}./dcs-docker-manager.sh pre-flight${NC} creates one for you"
            echo "   (Just a thought, no pressure...)"
            echo ""
        fi
        echo "$DEFAULT_PORT"
    fi
}

# Function to update .env file with new port
update_env_port() {
    local port=$1
    
    if [ -f "$ENV_FILE" ]; then
        # Update existing WEB_PORT
        if grep -q "^WEB_PORT=" "$ENV_FILE"; then
            # Linux
            sed -i "s/^WEB_PORT=.*/WEB_PORT=$port/" "$ENV_FILE"
        else
            echo "WEB_PORT=$port" >> "$ENV_FILE"
        fi
    else
        # Create new .env file
        echo "WEB_PORT=$port" > "$ENV_FILE"
    fi
}

# Function to check Docker installation
check_docker() {
    if ! command -v docker >/dev/null 2>&1; then
        print_error "Docker is not home right now..."
        echo "Please install Docker Desktop from: https://www.docker.com/products/docker-desktop/"
        return 1
    fi
    
    if ! docker info >/dev/null 2>&1; then
        print_error "Docker daemon is having a nap. Wake up Docker Desktop first!"
        return 1
    fi
    
    # Check for docker-compose or docker compose
    if command -v docker-compose >/dev/null 2>&1; then
        COMPOSE_CMD="docker-compose"
    elif docker compose version >/dev/null 2>&1; then
        COMPOSE_CMD="docker compose"
    else
        print_error "Docker Compose not found"
        return 1
    fi
    
    return 0
}

# Function to stop existing container
stop_existing_container() {
    if docker ps -a --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
        print_info "Stopping existing container..."
        $COMPOSE_CMD down >/dev/null 2>&1 || true
    fi
}

# Function to get external IP
get_external_ip() {
    # Try multiple services to get external IP
    local ip=""
    
    # Try curl first
    if command -v curl >/dev/null 2>&1; then
        ip=$(curl -s -4 --max-time 5 ifconfig.me 2>/dev/null || \
             curl -s -4 --max-time 5 icanhazip.com 2>/dev/null || \
             curl -s -4 --max-time 5 ipecho.net/plain 2>/dev/null || \
             curl -s -4 --max-time 5 api.ipify.org 2>/dev/null)
    fi
    
    # Try wget if curl failed
    if [ -z "$ip" ] && command -v wget >/dev/null 2>&1; then
        ip=$(wget -qO- -4 --timeout=5 ifconfig.me 2>/dev/null || \
             wget -qO- -4 --timeout=5 icanhazip.com 2>/dev/null)
    fi
    
    echo "$ip"
}

# Function to get local IP addresses
get_local_ips() {
    if command -v ip >/dev/null 2>&1; then
        # Linux
        ip -4 addr show | grep -oP '(?<=inet\s)\d+(\.\d+){3}' | grep -v '127.0.0.1'
    elif command -v ifconfig >/dev/null 2>&1; then
        # Linux/BSD
        ifconfig | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}' | sed 's/addr://'
    elif command -v hostname >/dev/null 2>&1; then
        # Alternative for some systems
        hostname -I 2>/dev/null | tr ' ' '\n' | grep -v '^$'
    else
        echo ""
    fi
}

# Function to display access information
display_access_info() {
    local port=$1
    
    echo ""
    echo -e "${GREEN}========================================"
    echo "DCS Statistics Website is running!"
    echo -e "========================================${NC}"
    echo ""
    echo "Access your site at:"
    echo -e "  Local:      ${CYAN}http://localhost:$port${NC}"
    
    # Get local network IPs
    local_ips=$(get_local_ips)
    if [ -n "$local_ips" ]; then
        echo "  Network:"
        for ip in $local_ips; do
            echo -e "              ${CYAN}http://$ip:$port${NC}"
        done
    fi
    
    # Get external IP
    external_ip=$(get_external_ip)
    if [ -n "$external_ip" ]; then
        echo -e "  External:   ${CYAN}http://$external_ip:$port${NC}"
        echo ""
        print_warning "Note: External access requires port forwarding on your router"
    fi
    
    echo ""
    echo -e "Admin Panel: ${YELLOW}http://localhost:$port/site-config/install.php${NC}"
    echo ""
    echo "To stop the server, run:"
    echo -e "  ${GRAY}./dcs-docker-manager.sh stop${NC}"
    echo "  or"
    echo -e "  ${GRAY}$COMPOSE_CMD down${NC}"
    echo ""
    
    # Check if port forwarding might be needed
    if [ -n "$external_ip" ]; then
        echo -e "${YELLOW}========================================"
        echo "Port Forwarding Instructions:"
        echo -e "========================================${NC}"
        echo "If you want external access, configure your router to:"
        echo "  1. Forward external port $port to internal port $port"
        echo "  2. Point to this machine's IP address"
        echo ""
        echo "Common router interfaces:"
        echo "  - http://192.168.1.1"
        echo "  - http://192.168.0.1"
        echo "  - http://10.0.0.1"
        echo "========================================"
    fi
}

# Function to detect Linux distribution
detect_distro() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
        VER=$VERSION_ID
    elif [ -f /etc/lsb-release ]; then
        . /etc/lsb-release
        OS=$DISTRIB_ID
        VER=$DISTRIB_RELEASE
    elif [ -f /etc/debian_version ]; then
        OS="debian"
        VER=$(cat /etc/debian_version)
    elif [ -f /etc/redhat-release ]; then
        if grep -q "Rocky" /etc/redhat-release; then
            OS="rocky"
        elif grep -q "AlmaLinux" /etc/redhat-release; then
            OS="almalinux"
        elif grep -q "CentOS" /etc/redhat-release; then
            OS="centos"
        else
            OS="rhel"
        fi
        VER=$(grep -oE '[0-9]+\.[0-9]+' /etc/redhat-release | head -1)
    else
        OS=$(uname -s)
        VER=$(uname -r)
    fi
    
    # Convert to lowercase
    OS=$(echo "$OS" | tr '[:upper:]' '[:lower:]')
}

# Function to install Docker based on distribution
install_docker() {
    local distro=$1
    
    print_info "Detected OS: $distro"
    echo ""
    
    case "$distro" in
        ubuntu|debian|linuxmint|pop)
            print_info "Installing Docker for Debian/Ubuntu-based system..."
            echo ""
            
            # Update package index
            print_info "Updating package index..."
            sudo apt-get update
            
            # Install prerequisites
            print_info "Installing prerequisites..."
            sudo apt-get install -y \
                ca-certificates \
                curl \
                gnupg \
                lsb-release
            
            # Add Docker's official GPG key
            print_info "Adding Docker GPG key..."
            sudo mkdir -p /etc/apt/keyrings
            
            # Determine the correct Docker repository URL
            local docker_distro=""
            case "$distro" in
                ubuntu|pop)
                    docker_distro="ubuntu"
                    ;;
                debian|linuxmint)
                    docker_distro="debian"
                    ;;
                *)
                    docker_distro="ubuntu"  # Default fallback
                    ;;
            esac
            
            curl -fsSL https://download.docker.com/linux/${docker_distro}/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
            
            # Set up repository
            print_info "Adding Docker repository..."
            echo \
                "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/${docker_distro} \
                $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
            
            # Install Docker Engine
            print_info "Installing Docker Engine..."
            sudo apt-get update
            sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
            
            # Add user to docker group
            print_info "Adding user to docker group..."
            sudo usermod -aG docker $USER
            
            # Start Docker
            print_info "Starting Docker service..."
            sudo systemctl start docker
            sudo systemctl enable docker
            
            print_success "Docker installed successfully!"
            ;;
            
        fedora)
            print_info "Installing Docker for Fedora..."
            echo ""
            
            # Remove old versions
            print_info "Removing old Docker versions if any..."
            sudo dnf remove -y docker \
                docker-client \
                docker-client-latest \
                docker-common \
                docker-latest \
                docker-latest-logrotate \
                docker-logrotate \
                docker-selinux \
                docker-engine-selinux \
                docker-engine
            
            # Install prerequisites
            print_info "Installing prerequisites..."
            sudo dnf -y install dnf-plugins-core
            
            # Add Docker repository
            print_info "Adding Docker repository..."
            sudo dnf config-manager --add-repo https://download.docker.com/linux/fedora/docker-ce.repo
            
            # Install Docker Engine
            print_info "Installing Docker Engine..."
            sudo dnf install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
            
            # Add user to docker group
            print_info "Adding user to docker group..."
            sudo usermod -aG docker $USER
            
            # Start Docker
            print_info "Starting Docker service..."
            sudo systemctl start docker
            sudo systemctl enable docker
            
            print_success "Docker installed successfully!"
            ;;
            
        centos|rhel|rocky|almalinux)
            print_info "Installing Docker for RHEL-based system..."
            echo ""
            
            # Remove old versions
            print_info "Removing old Docker versions if any..."
            sudo yum remove -y docker \
                docker-client \
                docker-client-latest \
                docker-common \
                docker-latest \
                docker-latest-logrotate \
                docker-logrotate \
                docker-engine
            
            # Install prerequisites
            print_info "Installing prerequisites..."
            sudo yum install -y yum-utils
            
            # Add Docker repository
            print_info "Adding Docker repository..."
            sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
            
            # Install Docker Engine
            print_info "Installing Docker Engine..."
            sudo yum install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
            
            # Add user to docker group
            print_info "Adding user to docker group..."
            sudo usermod -aG docker $USER
            
            # Start Docker
            print_info "Starting Docker service..."
            sudo systemctl start docker
            sudo systemctl enable docker
            
            print_success "Docker installed successfully!"
            ;;
            
        arch|manjaro|endeavouros)
            print_info "Installing Docker for Arch-based system..."
            echo ""
            
            # Update package database
            print_info "Updating package database..."
            sudo pacman -Sy
            
            # Install Docker
            print_info "Installing Docker..."
            sudo pacman -S --noconfirm docker docker-compose
            
            # Add user to docker group
            print_info "Adding user to docker group..."
            sudo usermod -aG docker $USER
            
            # Start Docker
            print_info "Starting Docker service..."
            sudo systemctl start docker
            sudo systemctl enable docker
            
            print_success "Docker installed successfully!"
            ;;
            
        opensuse*|suse*)
            print_info "Installing Docker for openSUSE/SUSE..."
            echo ""
            
            # Install Docker
            print_info "Installing Docker..."
            sudo zypper install -y docker docker-compose
            
            # Add user to docker group
            print_info "Adding user to docker group..."
            sudo usermod -aG docker $USER
            
            # Start Docker
            print_info "Starting Docker service..."
            sudo systemctl start docker
            sudo systemctl enable docker
            
            print_success "Docker installed successfully!"
            ;;
            
        *)
            print_error "Unsupported distribution: $distro"
            echo ""
            echo "Please install Docker manually from:"
            echo "https://docs.docker.com/engine/install/"
            echo ""
            echo "For generic Linux installation, you can try:"
            echo "  curl -fsSL https://get.docker.com -o get-docker.sh"
            echo "  sudo sh get-docker.sh"
            return 1
            ;;
    esac
    
    echo ""
    print_warning "IMPORTANT: You need to log out and back in for group changes to take effect!"
    echo "Or run: newgrp docker"
    echo ""
}

# Pre-flight check function
run_preflight() {
    echo "========================================"
    echo "Pre-Flight Check for DCS Statistics"
    echo "========================================"
    echo ""
    print_info "Running pre-flight checks and fixes..."
    echo ""
    
    # Detect OS
    detect_distro
    
    # Check Docker installation
    print_info "Checking Docker installation..."
    if ! command -v docker >/dev/null 2>&1; then
        print_warning "Docker is not installed"
        echo ""
        echo -n "Would you like to install Docker automatically? (y/N): "
        read -r response
        if [[ "$response" =~ ^[Yy]$ ]]; then
            install_docker "$OS"
            
            # Check if installation was successful
            if command -v docker >/dev/null 2>&1; then
                print_success "Docker has been installed"
                
                # Try to start docker daemon
                if ! docker info >/dev/null 2>&1; then
                    print_info "Starting Docker daemon..."
                    sudo systemctl start docker 2>/dev/null || true
                    sleep 2
                fi
            else
                print_error "Docker installation failed"
                return 1
            fi
        else
            print_error "Docker is required to run DCS Statistics"
            echo "Please install Docker manually from: https://docs.docker.com/engine/install/"
            return 1
        fi
    else
        print_success "Docker is installed"
    fi
    
    # Check if docker daemon is running
    if ! docker info >/dev/null 2>&1; then
        print_warning "Docker daemon is not running"
        print_info "Attempting to start Docker daemon..."
        
        # Try to start docker
        if command -v systemctl >/dev/null 2>&1; then
            sudo systemctl start docker 2>/dev/null && sleep 2
        elif command -v service >/dev/null 2>&1; then
            sudo service docker start 2>/dev/null && sleep 2
        fi
        
        # Check again
        if ! docker info >/dev/null 2>&1; then
            print_error "Failed to start Docker daemon"
            echo "Please start Docker manually with: sudo systemctl start docker"
            return 1
        fi
    fi
    print_success "Docker daemon is running"
    
    # Check Docker Compose
    print_info "Checking Docker Compose..."
    if ! command -v docker-compose >/dev/null 2>&1 && ! docker compose version >/dev/null 2>&1; then
        print_warning "Docker Compose not found"
        
        # Install docker-compose based on distro
        echo -n "Would you like to install Docker Compose? (y/N): "
        read -r response
        if [[ "$response" =~ ^[Yy]$ ]]; then
            print_info "Installing Docker Compose..."
            
            # Try to install via package manager first
            case "$OS" in
                ubuntu|debian|linuxmint|pop)
                    sudo apt-get update && sudo apt-get install -y docker-compose-plugin
                    ;;
                fedora)
                    sudo dnf install -y docker-compose-plugin
                    ;;
                centos|rhel|rocky|almalinux)
                    sudo yum install -y docker-compose-plugin
                    ;;
                arch|manjaro|endeavouros)
                    sudo pacman -S --noconfirm docker-compose
                    ;;
                *)
                    # Fallback to manual installation
                    print_info "Installing Docker Compose standalone..."
                    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
                    sudo chmod +x /usr/local/bin/docker-compose
                    ;;
            esac
            
            if command -v docker-compose >/dev/null 2>&1 || docker compose version >/dev/null 2>&1; then
                print_success "Docker Compose installed successfully!"
            else
                print_warning "Docker Compose installation may have failed"
            fi
        fi
    else
        print_success "Docker Compose is available"
    fi
    echo ""
    
    # Check and install dos2unix if needed
    if ! command -v dos2unix >/dev/null 2>&1; then
        print_warning "dos2unix not found (needed to fix line endings)"
        echo -n "Would you like to install dos2unix? (y/N): "
        read -r response
        if [[ "$response" =~ ^[Yy]$ ]]; then
            print_info "Installing dos2unix..."
            case "$OS" in
                ubuntu|debian|linuxmint|pop)
                    sudo apt-get install -y dos2unix
                    ;;
                fedora)
                    sudo dnf install -y dos2unix
                    ;;
                centos|rhel|rocky|almalinux)
                    sudo yum install -y dos2unix
                    ;;
                arch|manjaro|endeavouros)
                    sudo pacman -S --noconfirm dos2unix
                    ;;
                opensuse*|suse*)
                    sudo zypper install -y dos2unix
                    ;;
                *)
                    print_warning "Please install dos2unix manually for your distribution"
                    ;;
            esac
            
            if command -v dos2unix >/dev/null 2>&1; then
                print_success "dos2unix installed successfully!"
            fi
        fi
    fi
    
    # Fix line endings (if dos2unix is available)
    if command -v dos2unix >/dev/null 2>&1; then
        print_info "Fixing line endings for Docker files..."
        for file in *.sh Dockerfile* docker-compose*.yml .dockerignore .env*; do
            if [ -f "$file" ]; then
                dos2unix -q "$file" 2>/dev/null && echo "  Fixed: $file" || true
            fi
        done
        echo ""
    fi
    
    # Create required directories
    print_info "Ensuring required directories exist..."
    dirs=("./dcs-stats/data" "./dcs-stats/site-config/data" "./dcs-stats/backups")
    for dir in "${dirs[@]}"; do
        if [ ! -d "$dir" ]; then
            mkdir -p "$dir"
            print_success "Created directory: $dir"
        else
            echo "  Directory exists: $dir"
        fi
    done
    echo ""
    
    # Create or update .env file
    print_info "Checking .env file..."
    if [ ! -f "./.env" ]; then
        if [ -f "./.env.example" ]; then
            cp "./.env.example" "./.env"
            print_success "Created .env file from .env.example"
        else
            print_warning "No .env.example file found"
        fi
    else
        print_success ".env file exists"
        
        # Check if using old port 8080 and update to 9080
        if grep -q "WEB_PORT=8080" "./.env"; then
            sed -i "s/WEB_PORT=8080/WEB_PORT=9080/" "./.env"
            sed -i "s|SITE_URL=http://localhost:8080|SITE_URL=http://localhost:9080|" "./.env"
            print_success "Updated .env file from port 8080 to 9080"
        fi
    fi
    echo ""
    
    # Clean up Docker networks
    print_info "Cleaning up Docker networks..."
    docker network prune -f >/dev/null 2>&1
    print_success "Docker networks cleaned"
    echo ""
    
    print_success "Pre-flight check complete!"
    echo ""
    # Only show the "run start" message if we're not already in the start process
    if [ "$FROM_START" != "true" ]; then
        echo -e "You can now run: ${CYAN}./dcs-docker-manager.sh start${NC}"
    fi
}

# Destroy function
run_destroy() {
    local force_mode=false
    
    # Check if -f or --force flag was passed
    if [ "$1" = "-f" ] || [ "$1" = "--force" ]; then
        force_mode=true
    fi
    
    print_warning "This will DESTROY everything related to DCS Statistics Docker setup!"
    echo ""
    echo -e "${YELLOW}This action will remove:"
    echo "  - The DCS Statistics container"
    echo "  - The DCS Statistics Docker image"
    echo "  - All Docker volumes created by this project"
    echo "  - The Docker network (if created)"
    echo -e "  - Your .env configuration file${NC}"
    echo ""
    # Blinking cyan text for preserved data message
    echo -e "\033[5;36m[INFO] Your data in ./dcs-stats will be preserved\033[0m"
    echo ""
    
    # Skip confirmation if force mode is enabled
    if [ "$force_mode" = true ]; then
        print_info "Force mode enabled - skipping confirmation"
        confirmation="DESTROY"
    else
        echo -n "Type 'DESTROY' to confirm (or anything else to cancel): "
        read confirmation
    fi
    
    if [ "$confirmation" = "DESTROY" ]; then
        print_info "Starting destruction process..."
        
        # Stop and remove container
        print_info "Stopping and removing container..."
        $COMPOSE_CMD down -v >/dev/null 2>&1 || true
        
        # Remove the image
        print_info "Removing Docker image..."
        docker rmi dcs-statistics:latest >/dev/null 2>&1 || true
        docker rmi $(docker images -q -f "reference=dcs-statistics") >/dev/null 2>&1 || true
        
        # Clean up any dangling images
        print_info "Cleaning up dangling images..."
        docker image prune -f >/dev/null 2>&1
        
        # Remove any project-specific volumes
        print_info "Removing volumes..."
        docker volume rm $(docker volume ls -q -f "name=dcs-statistics") >/dev/null 2>&1 || true
        
        # Clean up networks
        print_info "Cleaning up networks..."
        docker network prune -f >/dev/null 2>&1
        
        # Remove .env file
        print_info "Removing .env configuration file..."
        if [ -f "./.env" ]; then
            rm -f "./.env"
            print_success "Removed .env file"
        fi
        
        print_success "Destruction complete!"
        echo ""
        echo -e "${GREEN}The following items were preserved:"
        echo -e "  - Your data in ./dcs-stats directory${NC}"
        echo ""
        echo -e "To completely start fresh, run:"
        echo -e "  ${CYAN}./dcs-docker-manager.sh pre-flight"
        echo -e "  ./dcs-docker-manager.sh start${NC}"
    else
        print_info "Destruction cancelled"
    fi
}

# Run sanitize command - removes EVERYTHING including data
run_sanitize() {
    local force_mode=false
    
    # Check if -f or --force flag was passed
    if [ "$1" = "-f" ] || [ "$1" = "--force" ]; then
        force_mode=true
    fi
    
    print_warning "*** COMPLETE SANITIZATION WARNING ***"
    echo ""
    echo -e "${RED}This will PERMANENTLY DELETE:"
    echo "  - The DCS Statistics container"
    echo "  - The DCS Statistics Docker image"
    echo "  - All Docker volumes and networks"
    echo "  - Your .env configuration file"
    echo "  - ALL DATA in ./dcs-stats/data directory"
    echo "  - ALL DATA in ./dcs-stats/site-config/data directory"
    echo -e "  - ALL BACKUPS in ./dcs-stats/backups directory${NC}"
    echo ""
    print_warning "*** THIS CANNOT BE UNDONE! ***"
    echo ""
    
    # Skip confirmation if force mode is enabled
    if [ "$force_mode" = true ]; then
        print_info "Force mode enabled - skipping confirmation"
        confirmation="SANITIZE"
    else
        echo -n "Type 'SANITIZE' to confirm complete data wipe (or anything else to cancel): "
        read confirmation
    fi
    
    if [ "$confirmation" = "SANITIZE" ]; then
        print_info "Starting complete sanitization..."
        
        # Stop and remove container
        print_info "Stopping and removing container..."
        $COMPOSE_CMD down -v >/dev/null 2>&1 || true
        
        # Remove the image
        print_info "Removing Docker image..."
        docker rmi dcs-statistics:latest >/dev/null 2>&1 || true
        docker rmi $(docker images -q -f "reference=dcs-statistics") >/dev/null 2>&1 || true
        
        # Clean up any dangling images
        print_info "Cleaning up dangling images..."
        docker image prune -f >/dev/null 2>&1
        
        # Remove any project-specific volumes
        print_info "Removing volumes..."
        docker volume rm $(docker volume ls -q -f "name=dcs-statistics") >/dev/null 2>&1 || true
        
        # Clean up networks
        print_info "Cleaning up networks..."
        docker network prune -f >/dev/null 2>&1
        
        # Remove .env file
        print_info "Removing .env configuration file..."
        if [ -f "./.env" ]; then
            rm -f "./.env"
            print_success "Removed .env file"
        fi
        
        # DELETE ALL DATA
        print_warning "Deleting ALL user data..."
        
        # Remove data directories
        if [ -d "./dcs-stats/data" ]; then
            print_info "Removing ./dcs-stats/data directory..."
            rm -rf "./dcs-stats/data"
            print_success "Removed data directory"
        fi
        
        if [ -d "./dcs-stats/site-config/data" ]; then
            print_info "Removing ./dcs-stats/site-config/data directory..."
            rm -rf "./dcs-stats/site-config/data"
            print_success "Removed site-config data directory"
        fi
        
        # Remove backups
        if [ -d "./dcs-stats/backups" ]; then
            print_info "Removing ./dcs-stats/backups directory..."
            rm -rf "./dcs-stats/backups"
            print_success "Removed backups directory"
        fi
        
        print_success "Complete sanitization finished!"
        echo ""
        print_warning "ALL DATA HAS BEEN PERMANENTLY DELETED"
        echo ""
        echo -e "To start completely fresh:"
        echo -e "  1. Run: ${CYAN}./dcs-docker-manager.sh pre-flight${NC}"
        echo -e "  2. Run: ${CYAN}./dcs-docker-manager.sh start${NC}"
        echo -e "  3. Complete setup at ${CYAN}http://localhost:9080/site-config/install.php${NC}"
    else
        print_info "Sanitization cancelled"
    fi
}

# Main startup function
start_dcs_statistics() {
    echo "========================================"
    echo "DCS Statistics Docker Launcher"
    echo "========================================"
    echo ""
    
    # Quick check for common issues
    needs_fix=false
    if [ ! -f "./.env" ] && [ -f "./.env.example" ]; then
        needs_fix=true
    fi
    if [ ! -d "./dcs-stats/data" ]; then
        needs_fix=true
    fi
    
    if [ "$needs_fix" = true ]; then
        print_warning "Hold up! Looks like this is your first rodeo..."
        echo -e "Just FYI: ${CYAN}./dcs-docker-manager.sh pre-flight${NC} exists for a reason"
        echo "   (It's like a pre-flight check, but cooler)"
        echo ""
    fi
    
    # Check Docker installation
    print_info "Checking Docker installation..."
    if ! check_docker; then
        print_error "Docker is not home right now..."
        echo -e "Once you get Docker Desktop installed, there's ${CYAN}./dcs-docker-manager.sh pre-flight${NC}"
        echo "   (It will make sure everything is perfect for your system)"
        exit 1
    fi
    print_success "Docker is installed and running"
    
    # Stop existing container if running
    stop_existing_container
    
    # Get desired port
    DESIRED_PORT=$(get_current_port)
    
    # Check if port is available
    print_info "Checking port $DESIRED_PORT availability..."
    
    if check_port $DESIRED_PORT; then
        print_success "Port $DESIRED_PORT is available"
        SELECTED_PORT=$DESIRED_PORT
    else
        print_warning "Port $DESIRED_PORT is in use"
        
        # Find alternative port
        print_info "Finding available port..."
        SELECTED_PORT=$(find_available_port $DESIRED_PORT)
        
        if [ -z "$SELECTED_PORT" ]; then
            print_error "No available ports found in range $DESIRED_PORT-$((DESIRED_PORT + 100))"
            echo "Wow, ALL those ports are taken? That's... impressive"
            echo -e "   Maybe ${CYAN}./dcs-docker-manager.sh pre-flight${NC} can help clear things up?"
            exit 1
        fi
        
        print_success "Using port $SELECTED_PORT instead"
    fi
    
    # Check if Docker image exists BEFORE updating .env
    if docker images | grep -q "^dcs-statistics.*latest"; then
        # Update .env file with selected port only if image already exists
        update_env_port $SELECTED_PORT
        print_success "Docker image exists, skipping build"
        print_info "Use './dcs-docker-manager.sh rebuild' to force a rebuild"
    else
        # If no Docker image exists, this is a first build - prompt the user
        echo ""
        print_warning "*** FIRST TIME SETUP DETECTED ***"
        echo ""
        echo "No Docker image found. This appears to be your first time building DCS Statistics."
        echo "Pre-flight checks are REQUIRED for first-time setup."
        echo ""
        echo "Pre-flight will:"
        echo "  - Create necessary directories"
        echo "  - Set up environment files"
        echo "  - Fix common permission issues"
        echo "  - Ensure Docker is properly configured"
        echo ""
        echo "Type CONTINUE to run pre-flight checks and proceed with setup"
        echo "Type anything else or press Enter to exit"
        echo ""
        echo -n "Your choice: "
        read -r response
        if [[ "$response" != "CONTINUE" ]]; then
            echo ""
            print_info "Setup cancelled. To set up DCS Statistics, either:"
            echo -e "  1. Run: ${CYAN}./dcs-docker-manager.sh pre-flight${NC} first, then"
            echo -e "     Run: ${CYAN}./dcs-docker-manager.sh start${NC}"
            echo "  OR"
            echo -e "  2. Run: ${CYAN}./dcs-docker-manager.sh start${NC} and type CONTINUE when prompted"
            exit 0
        fi
        echo ""
        print_info "Running pre-flight checks before continuing..."
        echo ""
        
        # Run pre-flight checks (set flag to suppress the "run start" message)
        FROM_START=true
        run_preflight
        if [ $? -ne 0 ]; then
            print_error "Pre-flight checks failed. Please fix the issues and try again."
            exit 1
        fi
        
        echo ""
        print_success "Pre-flight checks completed successfully!"
        echo ""
        
        # NOW update .env file with selected port after pre-flight has set up the file
        update_env_port $SELECTED_PORT
        
        print_info "Continuing with Docker build..."
        echo ""
        
        print_info "Docker image not found, building now..."
        print_info "This may take a few minutes on first run..."
        
        if $COMPOSE_CMD build --no-cache 2>&1 | tee /tmp/docker_build.log | grep -E "^#[0-9]+" ; then
            print_success "Docker image built successfully"
        else
            print_error "Failed to build Docker image"
            
            # Check for common issues
            if grep -q "invalid pool\|pool request" /tmp/docker_build.log 2>/dev/null; then
                print_warning "Oh snap! Network configuration went sideways!"
                echo -e "There's a script for that: ${CYAN}./dcs-docker-manager.sh pre-flight${NC}"
                echo "   (It literally fixes this in 2 seconds, just saying...)"
            elif grep -q "no such file\|not found" /tmp/docker_build.log 2>/dev/null; then
                print_warning "Uh-oh! Missing some directories here!"
                echo -e "Fun fact: ${CYAN}./dcs-docker-manager.sh pre-flight${NC} creates these for you"
                echo "   (But hey, who reads documentation, right?)"
            elif grep -q "/bin/sh\|exec format" /tmp/docker_build.log 2>/dev/null; then
                print_warning "Classic Windows vs Linux line endings drama!"
                echo -e "Psst... ${CYAN}./dcs-docker-manager.sh pre-flight${NC} sorts this out automatically"
                echo "   (Line endings being line endings, as usual...)"
            else
                echo "Well, that's a new one! Haven't seen this error before..."
                echo -e "   Maybe try ${CYAN}./dcs-docker-manager.sh pre-flight${NC} first? It fixes most things"
                echo "   (Or run $COMPOSE_CMD build for the gory details)"
            fi
            rm -f /tmp/docker_build.log
            exit 1
        fi
        rm -f /tmp/docker_build.log
    fi
    
    print_info "Starting container..."
    
    if $COMPOSE_CMD up -d 2>&1 | tee /tmp/docker_start.log | grep -v "^$" ; then
        print_success "Container started successfully"
    else
        print_error "Failed to start container"
        
        # Check for common issues
        if grep -q "permission denied\|access denied" /tmp/docker_start.log 2>/dev/null; then
            print_warning "Permission denied! The Docker gods are angry!"
            echo -e "Plot twist: ${CYAN}./dcs-docker-manager.sh pre-flight${NC} handles permissions"
            echo "   (I know, I know... should've mentioned it earlier)"
        elif grep -q "network.*not found" /tmp/docker_start.log 2>/dev/null; then
            print_warning "Docker networks playing hide and seek again!"
            echo -e "Pro tip: ${CYAN}./dcs-docker-manager.sh pre-flight${NC} cleans these up"
            echo "   (It's like a spa day for your Docker networks)"
        elif grep -q "port is already allocated\|bind.*address already in use" /tmp/docker_start.log 2>/dev/null; then
            print_warning "Port $SELECTED_PORT is being a diva - says it's already taken!"
            echo "That's awkward... I usually catch this. Try running again?"
            echo "   (Sometimes ports are just moody like that)"
        else
            echo "Something weird happened... and not the good kind of weird"
            echo -e "   First aid kit: ${CYAN}./dcs-docker-manager.sh pre-flight${NC}"
            echo "   (If that doesn't help, run $COMPOSE_CMD up for the full drama)"
        fi
        rm -f /tmp/docker_start.log
        exit 1
    fi
    rm -f /tmp/docker_start.log
    
    # Wait for service to be ready
    print_info "Waiting for service to be ready..."
    sleep 3
    
    # Check if service is responding
    if curl -s -f -o /dev/null --max-time 5 http://localhost:$SELECTED_PORT/health-check.php 2>/dev/null; then
        print_success "Service is ready!"
    else
        print_warning "Service is being shy... might still be waking up"
        echo -e "Check the logs with: ${CYAN}./dcs-docker-manager.sh logs${NC}"
        echo "   (Or just wait a sec and refresh the browser)"
    fi
    
    # Always display access info
    display_access_info $SELECTED_PORT
}

# Detect docker-compose command
if command -v docker-compose >/dev/null 2>&1; then
    COMPOSE_CMD="docker-compose"
elif docker compose version >/dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
else
    COMPOSE_CMD=""
fi

# Function to rebuild Docker image
rebuild_image() {
    echo "========================================"
    echo "Rebuilding DCS Statistics Docker Image"
    echo "========================================"
    echo ""
    
    # Check Docker
    print_info "Checking Docker..."
    if ! check_docker; then
        print_error "Docker is not available"
        exit 1
    fi
    
    # Stop existing container if running
    stop_existing_container
    
    # Remove old image
    print_info "Removing old Docker image..."
    docker rmi dcs-statistics:latest 2>/dev/null || true
    docker rmi $(docker images -q -f "reference=*dcs-statistics*") 2>/dev/null || true
    print_success "Old image removed"
    
    # Build new image
    print_info "Building new Docker image..."
    print_info "This will take a few minutes..."
    
    if $COMPOSE_CMD build --no-cache 2>&1 | tee /tmp/docker_build.log | grep -E "^#[0-9]+" ; then
        print_success "Docker image rebuilt successfully!"
        rm -f /tmp/docker_build.log
        echo ""
        echo -e "You can now start the container with: ${CYAN}./dcs-docker-manager.sh start${NC}"
    else
        print_error "Failed to rebuild Docker image"
        echo "Check the full output above for errors"
        rm -f /tmp/docker_build.log
        exit 1
    fi
}

# Function to show help menu
show_help() {
    echo "========================================"
    echo -e "${CYAN}DCS Statistics Docker Manager${NC}"
    echo "========================================"
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo -e "${GREEN}Available Commands:${NC}"
    echo ""
    echo -e "  ${CYAN}pre-flight${NC}  - Run pre-flight checks and auto-install Docker if needed"
    echo -e "  ${CYAN}start${NC}       - Start DCS Statistics container (builds only if needed)"
    echo -e "  ${CYAN}stop${NC}        - Stop DCS Statistics container"
    echo -e "  ${CYAN}restart${NC}     - Restart DCS Statistics container"
    echo -e "  ${CYAN}rebuild${NC}     - Force rebuild of Docker image"
    echo -e "  ${CYAN}status${NC}      - Check if container is running"
    echo -e "  ${CYAN}logs${NC}        - Show container logs (last 100 lines)"
    echo -e "  ${CYAN}destroy${NC}     - Remove everything except your data (add -f to skip confirmation)"
    echo -e "  ${CYAN}sanitize${NC}    - Remove EVERYTHING including all data (add -f to skip confirmation)"
    echo -e "  ${CYAN}help${NC}        - Show this help menu"
    echo ""
    echo -e "${YELLOW}Quick Start:${NC}"
    echo "  1. Run './dcs-docker-manager.sh pre-flight' to set up everything"
    echo "  2. Run './dcs-docker-manager.sh start' to launch the application"
    echo ""
    echo -e "${YELLOW}First Time Users:${NC}"
    echo "  Start with 'pre-flight' - it will:"
    echo "  - Check and install Docker if needed"
    echo "  - Set up required directories"
    echo "  - Configure environment files"
    echo "  - Fix any line ending issues"
    echo ""
    echo "========================================"
}

# Handle script arguments
ACTION="${1:-}"

# Show help if no arguments provided
if [ -z "$ACTION" ]; then
    show_help
    exit 0
fi

case "$ACTION" in
    start)
        start_dcs_statistics
        ;;
    stop)
        print_info "Stopping DCS Statistics..."
        $COMPOSE_CMD down
        print_success "Stopped"
        ;;
    restart)
        print_info "Restarting DCS Statistics..."
        $COMPOSE_CMD down
        start_dcs_statistics
        ;;
    rebuild)
        rebuild_image
        ;;
    status)
        if docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
            PORT=$(get_current_port)
            print_success "DCS Statistics is running on port $PORT"
            echo "Access at: http://localhost:$PORT"
        else
            print_info "DCS Statistics is not running"
        fi
        ;;
    logs)
        print_info "Showing last 100 lines of logs..."
        echo ""
        docker logs --tail 100 $CONTAINER_NAME
        echo ""
        print_info "End of logs. Use 'docker logs -f $CONTAINER_NAME' if you want to follow live logs."
        ;;
    pre-flight)
        run_preflight
        ;;
    destroy)
        run_destroy "$2"
        ;;
    sanitize)
        run_sanitize "$2"
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        print_error "Unknown command: $ACTION"
        echo ""
        show_help
        exit 1
        ;;
esac