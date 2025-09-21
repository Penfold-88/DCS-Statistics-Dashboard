# 🎖️ DCS Statistics Dashboard

**Transform your DCS server data into a stunning, interactive web dashboard with real-time API integration!**

[![Live Analytics](https://img.shields.io/badge/🌐_Live_Analytics-Real_Time_Data-blue?style=for-the-badge)](http://stats.skypirates.uk)
[![DCSServerBot](https://img.shields.io/badge/🤖_Requires-DCSServerBot-green?style=for-the-badge)](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot)
[![Security](https://img.shields.io/badge/🔒_Security-Enterprise_Grade-red?style=for-the-badge)](#-security-features)
[![Responsive](https://img.shields.io/badge/📱_Design-Fully_Responsive-purple?style=for-the-badge)](#-responsive-design)

## 🎯 What's New in v1.0.0

### 🚀 **Advanced Admin Panel**
- 🎛️ **Role-Based Access Control** - Multi-tier permission system (Air Boss, Squadron Leader, Pilot)
- 🔐 **Secure Authentication** - Modern login system with session management
- 🔄 **Auto-Update System** - One-click updates from GitHub with version tracking
- 💾 **Backup & Restore** - Automatic backups before updates with version metadata
- 🎨 **Theme Manager** - Pre-built themes (Sky Pirates, Grim Reapers, Blue Angels, more!)

### ✨ **Modern Professional Interface**
- 🖼️ **Cinematic Header** - Epic DCS combat scene background with professional overlay
- 🎨 **Unified Design System** - Consistent cards, buttons, and styling across all pages
- 📱 **Dynamic Responsive Layout** - Adapts fluidly to any screen size (98% mobile width to 1400px desktop)
- 🔍 **Unified Search Experience** - Consistent search bars with advanced functionality

### 🛡️ **Advanced Features**
- 📊 **Feature Management** - Granular control over every dashboard element
- 🌐 **Enhanced API Client** - Bulletproof error handling and retry logic
- 🐳 **Zero-Config Docker** - Complete containerized deployment with auto-setup
- 📈 **Performance Monitoring** - Built-in API health checks and status indicators

## 📸 Modern Dashboard Preview

Experience a professional-grade statistics platform featuring:
- 🏆 **Top 10 Leaderboards** with trophy displays and combat rankings
- 💰 **Credits System** with unified pilot card interface
- 👨‍✈️ **Individual Pilot Profiles** with dynamic statistics and combat charts
- 🛡️ **Squadron Management** with member tracking (optional)
- 🖥️ **Live Server Status** with mission info and mod displays
- 🎯 **Unified Search** - Find pilots instantly across all pages

## ⚡ Quick Start

### 🔧 Prerequisites
- ✅ [**DCSServerBot by Special K**](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot/releases) with REST API enabled
- ✅ **PHP 8.3+ web server** OR **Docker**
- ✅ **Web hosting** (shared hosting works Requires Port Forwarding Not All Hosts allow)

### 🚀 Installation Options

#### Option 1: Traditional Web Hosting

1. **Download** the latest release and extract
2. **Upload** the `dcs-stats/` folder to your web server
3. **Access** `https://yourdomain.com/dcs-stats/`
4. **Follow the setup wizard** to create your admin account
5. **Configure** your DCSServerBot API endpoint

#### Option 2: Docker Deployment (Zero Configuration!)

```bash
# Clone the repository
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.git
cd DCS-Statistics-Dashboard

# Start with Docker (that's it!)
docker compose up -d

# Access at http://localhost:8080
```

The Docker setup automatically:
- ✅ Creates all required directories
- ✅ Sets proper permissions
- ✅ Initializes the database
- ✅ Configures the web server
- ✅ No manual configuration needed!

### ⚙️ First-Time Setup

1. **Access your dashboard** at `http://yourdomain.com/dcs-stats/`
2. **Click "Start Setup"** on the welcome screen
3. **Create your admin account** (you'll be the Air Boss!)
4. **Configure DCSServerBot API**:
   - Enter your API URL (e.g., `http://localhost:8080`)
   - Test the connection
   - Save configuration
5. **Customize your dashboard**:
   - Choose a theme
   - Enable/disable features
   - Set your Discord link

**🎉 That's it!** Your dashboard now displays real-time data from DCSServerBot.

#### Option 3: Xampp  (Minimal Configuration!)

A Full Howto on this can be found in the wiki https://github.com/Penfold-88/DCS-Statistics-Dashboard/wiki

## 🎛️ Admin Panel Features

### 🔐 Secure Access
Access the admin panel at `/dcs-stats/site-config/` (NOT `/admin`!)

### 👥 Role-Based Permissions

| Role | Dashboard Access | API Config | Updates | User Management | Themes |
|------|-----------------|------------|---------|-----------------|---------|
| **Air Boss** | ✅ Full | ✅ | ✅ | ✅ | ✅ |
| **Squadron Leader** | ✅ View | ❌ | ❌ | ✅ Limited | ✅ |
| **Pilot** | ✅ View Only | ❌ | ❌ | ❌ | ❌ |

### 🚀 Auto-Update System

1. **Version Tracking** - Know exactly what version you're running
2. **Update Notifications** - Get alerts when updates are available
3. **One-Click Updates** - Update directly from the admin panel
4. **Automatic Backups** - Creates backup before every update
5. **Version History** - Track all updates and changes
6. **Branch Support** - Switch between stable and development branches

### 💾 Backup Management

- **Automatic Backups** - Before updates and on schedule
- **Manual Backups** - Create snapshots anytime
- **Version Metadata** - Each backup includes version and branch info
- **Easy Restore** - One-click restore to any backup
- **Auto-Cleanup** - Keeps only the 5 most recent backups
- **Download Backups** - Export for external storage

### 🎨 Theme System

Pre-built professional themes included:
- 🏴‍☠️ **Sky Pirates** - Dark theme with green accents
- 💀 **Grim Reapers** - High contrast red theme
- 🔵 **Blue Angels** - Navy blue professional theme
- 🌊 **Navy** - Classic military styling
- 🎖️ **Air Force** - Light blue aviation theme
- 🔥 **Danger Zone** - Bold orange accents
- 🌙 **Night Ops** - Ultra-dark stealth mode
- ❄️ **Arctic** - Cool blue winter theme

## 🐳 Docker Deployment

### Zero-Configuration Setup

Our Docker setup is completely automated - just run and go!

```bash
# Clone the repository
git clone https://github.com/Penfold-88/DCS-Statistics-Dashboard.git
cd DCS-Statistics-Dashboard

# Start the container (that's it!)
docker compose up -d

# Access at http://localhost:8080
```

### What Docker Does Automatically

✅ **Directory Creation** - All folders created with correct permissions  
✅ **File Permissions** - Automatically set for web server access  
✅ **Database Init** - User database created if not exists  
✅ **Config Templates** - Default configs generated  
✅ **Health Checks** - Monitors container status  
✅ **Auto-Restart** - Recovers from crashes  
✅ **Volume Persistence** - Data survives container updates  

### Docker Commands

```bash
# View logs
docker compose logs -f

# Stop the container
docker compose down

# Update to latest version
docker compose pull
docker compose up -d

# Access container shell
docker compose exec dcs-stats-web bash
```

### Custom Ports

Edit `.env` file:
```bash
# Change from default 8080
WEB_PORT=8090
```

## 🔒 Security Features

### Multi-Layer Protection
✅ **Authentication System** - Secure login with bcrypt password hashing  
✅ **Session Management** - Secure session handling with CSRF tokens  
✅ **Role-Based Access** - Granular permissions for every feature  
✅ **XSS Prevention** - All inputs sanitized and escaped  
✅ **Rate Limiting** - API abuse prevention with throttling  
✅ **Security Headers** - CSP, XSS protection, clickjacking prevention  
✅ **Input Validation** - Comprehensive data filtering  
✅ **Secure File Access** - Protected directories and files  

### Admin Security
- Password strength requirements
- Failed login tracking
- Session timeout
- Activity logging
- IP-based restrictions (optional)

## 🎯 Feature Management

### Granular Control System

Control exactly what your community sees:

```php
// Homepage Features
'home_server_stats' => true,      // Server statistics cards
'home_top_pilots' => true,        // Top 5 pilots chart
'home_mission_stats' => true,     // Combat statistics
'home_player_activity' => true,   // Activity overview

// Combat Statistics
'pilot_combat_stats' => true,     // Kills, deaths, K/D ratio
'pilot_flight_stats' => true,     // Takeoffs, landings, crashes
'pilot_session_stats' => true,    // Last session data
'pilot_aircraft_chart' => true,   // Aircraft usage charts

// Credits System  
'credits_enabled' => true,        // Enable credits system
'credits_leaderboard' => true,    // Credits rankings

// Squadron Features
'squadrons_enabled' => true,      // Squadron system
'squadron_management' => true,    // Squadron admin tools
'squadron_statistics' => true,    // Squadron stats
```

## 🔧 Troubleshooting Guide

### 🔍 **Admin Panel Access**
- The admin panel is at `/dcs-stats/site-config/` (NOT `/admin`)
- First user to register becomes the Air Boss
- Default permissions are set during first setup

### 📊 **API Connection Issues**
```bash
# Test API directly
curl http://localhost:8080/ping

# Check admin panel
Dashboard → API Configuration → Test Connection

# For Docker users
Use http://host.docker.internal:8080 on Windows/Mac
Use http://172.17.0.1:8080 on Linux
```

### 🎨 **Theme Not Applying**
1. Clear browser cache (Ctrl+F5)
2. Check theme selection saved in admin panel
3. Verify CSS file permissions
4. Check browser console for errors

### 🔄 **Update Failures**
1. Check file permissions on web server
2. Ensure backup directory is writable
3. ```extension=zip``` Enabled in your ```php.ini``` example of a disabled extention is ```;extension=zip```
4. Verify GitHub connectivity
5. Check PHP error logs
6. Manual update via Docker: `docker compose pull && docker compose up -d`

## 📁 Project Structure

```
DCS-Statistics-Dashboard/
├── 📁 dcs-stats/                  # Main web application
│   ├── 📁 site-config/            # Admin panel (NEW!)
│   │   ├── 🔐 index.php          # Admin dashboard
│   │   ├── 🎨 themes.php         # Theme manager
│   │   ├── 🔄 update.php         # Update system
│   │   ├── 💾 backups.php        # Backup management
│   │   ├── 👥 users.php          # User management
│   │   └── 📁 api/               # Admin API endpoints
│   ├── 🏠 index.php              # Homepage 
│   ├── 🏆 leaderboard.php        # Combat rankings
│   ├── 💰 pilot_credits.php      # Credits leaderboard
│   ├── 👨‍✈️ pilot_statistics.php   # Pilot profiles
│   ├── 🛡️ squadrons.php          # Squadron system
│   ├── 🖥️ servers.php            # Server status
│   └── 🎨 themes/                # Theme files
├── 🐳 Dockerfile                 # Production container
├── 🐳 docker-compose.yml         # Docker orchestration
└── 📚 README.md                  # This guide
```

## 🤝 Contributing

We welcome contributions from the DCS community!

### Development Setup
```bash
# Clone repository
git clone https://github.com/Penfold-88/DCS-Statistics-Website-Uploader.git

# Create feature branch
git checkout -b feature/amazing-feature

# Make changes and test
# Submit pull request
```

### Contribution Guidelines
- ✅ Follow existing code patterns
- ✅ Test responsive design
- ✅ Ensure security best practices
- ✅ Update documentation
- ✅ Include screenshots for UI changes

## 📄 License & Credits

### License
This project is licensed under the **MIT License** - see [LICENSE](LICENSE) file.

### 🙏 Acknowledgments
- **DCSServerBot** by [Special K](https://github.com/Special-K-s-Flightsim-Bots/DCSServerBot) - The foundation of this system
- **Sky Pirates Squadron** - Original development and testing
- **DCS Community** - Continuous feedback and improvements
- **Eagle Dynamics** - For creating DCS World

---

## 🚀 Get Started Today

**⭐ Star this repository** if it helps your community!  
**🐛 Report issues** to help improve the platform  
**💬 Share with other** DCS server administrators  
**🎮 Join the community** and showcase your dashboard

### Support Links
- 💬 [**Discord Support**](https://discord.gg/uTk8uQ2hxC) - Get help and chat with the community
- 📖 [**Documentation**](https://github.com/Penfold-88/DCS-Statistics-Website-Uploader/wiki)
- 🐛 [**Issue Tracker**](https://github.com/Penfold-88/DCS-Statistics-Website-Uploader/issues)
- 🌐 [**Live Demo**](https://stats.skypirates.uk/)

**Transform your DCS server into a professional gaming platform today!** 🎖️
