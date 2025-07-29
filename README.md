# 🎖️ DCS Statistics Website Uploader

**Transform your DCS server data into a beautiful, interactive web dashboard for your gaming community!**

[![Live Demo](https://img.shields.io/badge/🌐_Live_Demo-View_Website-blue?style=for-the-badge)](http://skypirates.uk/DCS-Stats-Demo/dcs-stats/)
[![DCSServerBot](https://img.shields.io/badge/🤖_Requires-DCSServerBot-green?style=for-the-badge)](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot)
[![Security](https://img.shields.io/badge/🔒_Security-Enhanced-red?style=for-the-badge)](#-security-features)

## 📸 Preview

Create stunning statistics dashboards featuring:
- 🏆 **Interactive Leaderboards** with top pilot rankings
- 💰 **Credit Systems** with trophy displays  
- 👨‍✈️ **Individual Pilot Profiles** with comprehensive stats
- 🛡️ **Squadron Management** with member tracking
- 🖥️ **Live Server Status** with mission info and mods

## 🎯 What Does This Do?

This system automatically transforms your DCS server data into a professional website that your community will love:

```
Your DCS Server → DCSServerBot → Auto Upload → Beautiful Website
```

**🔄 Fully Automated:** Set it up once, get hourly updates forever  
**🎨 Professional Design:** Dark theme with responsive mobile support  
**🔍 Rich Features:** Search players, view detailed stats, track squadrons  
**🔒 Secure:** Built-in security features and XSS protection  

## ⚡ Quick Start

### 1️⃣ Prerequisites
- ✅ [**DCSServerBot by Special K**](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot/releases) (with dbexporter module)
- ✅ **Python 3.13.3+** (already installed with DCSServerBot)
- ✅ **PHP 8.3+ web server** with FTP access
- ✅ **Web hosting** (shared hosting works fine!)

### 2️⃣ Install DCSServerBot Export Module

```bash
# Follow the official guide to install the dbexporter module
```
📖 [**DCSServerBot dbexporter Documentation**](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot/blob/master/plugins/dbexporter/README.md)

### 3️⃣ Setup Website Files

1. **Download** the latest release and extract it
2. **Upload** the `dcs-stats/` folder to your web server
3. **Verify** you can access `https://yourdomain.com/dcs-stats/`

### 4️⃣ Configure the Auto-Uploader

#### Option A: Secure Environment Variables (Recommended 🔒)
```bash
# Copy the template
cp Stats-Uploader/.env.example Stats-Uploader/.env

# Edit .env file with your settings
FTP_HOST=your.ftp.server.com
FTP_USER=your_ftp_username  
FTP_PASSWORD=your_ftp_password
FTP_SECURE=true
LOCAL_FOLDER=/path/to/DCSServerBot/export
REMOTE_FOLDER=/data
```

#### Option B: Configuration File
Edit `Stats-Uploader/config.ini`:
```ini
[Paths]
local_folder = /path/to/DCSServerBot/export
remote_folder = /data

[FTP]  
host = your.ftp.server.com
user = your_ftp_username
password = your_ftp_password
secure = true
```

### 5️⃣ Install Dependencies & Run

```bash
# Install required Python packages
pip install -r Stats-Uploader/requirements.txt

# Start the uploader (runs forever)
python Stats-Uploader/uploader.py
```

**🎉 That's it!** Your website will update automatically every hour with fresh data.

## 🌟 Website Features

### 🏠 **Dashboard Pages**
| Page | Description | Key Features |
|------|-------------|--------------|
| **🏆 Leaderboard** | Combat rankings | Top 3 display, kills/sorties tracking |
| **💰 Pilot Credits** | Points system | Trophy winners, searchable table |
| **👨‍✈️ Pilot Statistics** | Individual lookup | Complete profile, squadron info |
| **🛡️ Squadrons** | Group management | Member lists, squadron leaderboards |
| **🖥️ Servers** | Live status | Current missions, installed mods |

### 🔍 **Interactive Features**
- **Real-time Search:** Find any pilot instantly
- **Responsive Design:** Works on desktop, tablet, and mobile
- **Live Data:** Updates every hour automatically  
- **Trophy Displays:** Highlight top performers
- **Pagination:** Handle large datasets smoothly

## 📊 Data Sources

The system uses **28 different data files** from DCSServerBot. **Default uploads** include:

| File | Purpose | Contains |
|------|---------|----------|
| `players.json` | Player database | Names, UCIDs, identification |
| `missionstats.json` | Combat events | Kills, takeoffs, landings, crashes |
| `credits.json` | Points system | Player credits/points earned |
| `instances.json` | Server Statistics | DCS servers |
| `missions.json` | Server Statistics | map/theatre used|
| `mm_packages.json` | Server Statistics | Modules installed on DCS server |
| `squadrons.json` | Squadron Statistics | Registered squadrons |
| `squadron_credits.json` | Squadron Statistics | Credits Earned by squadron |
| `squadron_members.json` | Squadron Statistics | Members of squadron |

**📁 Optional data sources: 20+ other data types available.

## 🔧 Customization

### 🎨 **Branding**
Update these files to match your community:
- **`dcs-stats/nav.php`** - Change Discord link
- **`dcs-stats/header.php`** - Update site title  
- **`dcs-stats/styles.css`** - Customize colors and styling

### ⚙️ **Upload Settings**
Control what data gets uploaded in `config.ini`:
```ini
[Files]
credits.json = true          # Enable credits system
missionstats.json = true     # Enable combat statistics  
players.json = true          # Enable player database
squadrons.json = false       # Disable squadron features
missions.json = false        # Disable mission tracking
# ... 25+ more options
```

### 🔄 **Update Frequency**
```ini
[Upload]
throttle_seconds = 1         # Delay between file uploads
display_countdown = true     # Show countdown timer
```

## 🔒 Security Features

This system includes **enterprise-grade security:**

✅ **XSS Protection** - All user inputs sanitized  
✅ **SQL Injection Prevention** - No database = no SQL attacks  
✅ **Rate Limiting** - API abuse protection  
✅ **Secure FTP** - FTPS encryption by default  
✅ **Input Validation** - Comprehensive data filtering  
✅ **Security Headers** - XSS, clickjacking, MIME protection  
✅ **Access Controls** - Direct file access blocked  

## 🌐 Integration Options

### 📱 **Embed in Your Website**
Use the provided iframe code:
```html
<iframe src="https://yourdomain.com/dcs-stats" 
        width="100%" height="600px" frameborder="0">
</iframe>
```

### 🔗 **API Access**
Direct API endpoints available:
- `get_leaderboard.php` - Combat rankings
- `get_credits.php` - Credits leaderboard  
- `get_player_stats.php?name=PlayerName` - Individual stats

## 🚨 Troubleshooting

### **❌ Website shows "No data"**
1. Check that DCSServerBot dbexporter is running
2. Verify uploader.py is uploading files successfully
3. Confirm files are in the `/data` folder on your web server

### **❌ Uploader fails to connect**
1. Verify FTP credentials are correct
2. Test FTP connection manually
3. Check if FTPS is supported (try `FTP_SECURE=false`)

### **❌ Search doesn't find players**
1. Ensure `players.json` is being uploaded
2. Check player names are exact matches (case-insensitive)
3. Wait for next hourly update cycle

### **📧 Still need help?**
- Check the [**FIXES.md**](FIXES.md) for security documentation
- Review DCSServerBot logs for export issues
- Verify web server PHP error logs

## 🏗️ System Architecture

```
┌─────────────┐    ┌──────────────┐    ┌─────────────┐    ┌──────────────┐
│ DCS Server  │───▶│ DCSServerBot │───▶│ JSON Export │───▶│ Auto Uploader│
└─────────────┘    └──────────────┘    └─────────────┘    └──────────────┘
                                                                   │
┌─────────────┐    ┌──────────────┐    ┌─────────────┐           │
│ Your Users  │◀───│ PHP Website  │◀───│ Web Server  │◀──────────┘
└─────────────┘    └──────────────┘    └─────────────┘
```

## 📋 File Structure
```
DCS-Statistics-Website-Uploader/
├── 📁 Stats-Uploader/           # Python uploader
│   ├── 🐍 uploader.py          # Main upload script
│   ├── ⚙️ config.ini           # Configuration file
│   ├── 🔒 .env.example         # Secure credentials template
│   └── 📦 requirements.txt     # Python dependencies
├── 📁 dcs-stats/               # Website files  
│   ├── 🏠 index.php           # Homepage
│   ├── 🏆 leaderboard.php     # Combat rankings
│   ├── 💰 pilot_credits.php   # Credits system
│   ├── 👨‍✈️ pilot_statistics.php # Individual lookup
│   ├── 🛡️ squadrons.php       # Squadron management
│   ├── 🖥️ servers.php         # Server status
│   ├── 📁 data/               # JSON files uploaded here
│   └── 🎨 styles.css          # Website styling
├── 📁 integrations/            # Embedding tools
└── 📚 FIXES.md                # Security documentation
```

## 🤝 Contributing

Found a bug or want to add features? Contributions welcome!

1. Fork the repository
2. Create a feature branch
3. Test your changes thoroughly  
4. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Credits

- **DCSServerBot** by [Special K](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot) - The foundation that makes this possible
- **Sky Pirates Squadron** - Original development and testing
- **DCS Community** - Feedback and feature requests

---

**⭐ Star this repo if it helped your community!**  
**🐛 Report issues to help us improve**  
**💬 Share with other DCS server admins**

