# 📋 Changelog

All notable changes to the DCS Statistics Website Uploader project.

## [Unreleased] - 2025-07-28

### 🐛 Bug Fixes & Improvements

#### Server Statistics Data Calculation
**What Changed:** Fixed server statistics to use correct event types from actual data structure
**Why:** The dashboard was showing incorrect statistics due to mismatched event types and incorrect data field references.

**Technical Fixes:**
- Changed visit counting from S_EVENT_MISSION_START to S_EVENT_TAKEOFF (actual server activity)
- Updated kill counting to use S_EVENT_HIT events instead of S_EVENT_KILL
- Enhanced death tracking to include all death-related events:
  - S_EVENT_CRASH
  - S_EVENT_EJECTION
  - S_EVENT_PILOT_DEAD
  - S_EVENT_KILL (when target is an airplane)
- Removed artificial fallback logic that was inflating visit counts

#### Chart Alignment Fixes
**What Changed:** Fixed left-aligned charts on dashboard and pilot statistics pages
**Why:** Charts and content were aligned to the left side of the page instead of being properly centered, affecting the visual presentation.

**Technical Fixes:**
- Added proper centering to main element on both pages
- Set max-width: 1400px for index.php to accommodate wider dashboard layout
- Set max-width: 1200px for pilot_statistics.php for optimal readability
- Maintains responsive design with proper centering on all screen sizes

#### Carrier Trap Tracking Fixes
**What Changed:** Fixed carrier trap statistics showing 0 for pilots with actual traps
**Why:** The system was looking for incorrect field names in traps.json, causing all trap counts to show as zero even for pilots with multiple carrier landings.

**Technical Fixes:**
- Corrected UCID field lookup to use `player_ucid` from actual data format
- Implemented proper Navy grading system mapping:
  - OK = 4.0 (Perfect pass)
  - Fair = 3.0-3.9
  - No Grade = 2.0-2.9
  - C (Cut) = 1.0-1.9 (Dangerous approach)
  - WO (Wave Off) = 0-0.9
- Added wire-based score adjustments (3-wire is perfect, others get deductions)
- Inverted points field logic (0 = good trap, 1 = wave off)

#### Carrier Landing Performance Chart
**What Changed:** Added new performance distribution chart for carrier landings
**Why:** Pilots need to track their carrier landing proficiency over time. Visual representation helps identify areas for improvement.

**Features Added:**
- Bar chart showing distribution of landing grades
- Color-coded grades matching Navy standards (green to red)
- Percentage breakdown in tooltips
- Average trap score display
- Only shows for pilots with carrier operations

#### Dashboard Data Display Fixes
**What Changed:** Fixed empty charts for top pilots and squadrons on main dashboard
**Why:** Charts were showing blank when no "S_EVENT_MISSION_START" events existed, making the dashboard appear broken.

**Improvements:**
- Fallback to count any player activity as a visit when no mission starts exist
- Show "No mission data available yet" message instead of empty charts
- Graceful handling of empty squadron data
- Proper null/zero value handling in all calculations

#### Layout & Centering Improvements
**What Changed:** Centered all graphs and improved responsive layouts
**Why:** Charts were left-aligned and looked unbalanced on larger screens. Mobile layouts were breaking on some devices.

**Visual Improvements:**
- Centered graph containers with max-width constraints
- Index dashboard: 1200px max-width, 2-column grid
- Pilot statistics: 1000px max-width, 2-column grid
- Improved mobile breakpoints (collapses to 1 column below 768px)
- Better spacing and padding for visual hierarchy

#### Search Result Filtering
**What Changed:** Filter out players with no statistics from search results
**Why:** Search was returning all players in the database, including those who never flew, cluttering results with irrelevant entries.

**Implementation:**
- Only returns players with mission events or trap data
- Checks both missionstats.json and traps.json for activity
- Updated message: "No pilots with recorded statistics found"
- Significantly cleaner and more relevant search results

### 🎯 Server Dashboard Transformation

#### Interactive Statistics Dashboard
**What Changed:** Transformed index.php from a simple welcome page into a comprehensive server statistics dashboard
**Why:** Users needed immediate visibility into server health and activity metrics. A dashboard provides at-a-glance insights into server performance and player engagement without navigating through multiple pages.

**Dashboard Features:**
- **4 Animated Stat Cards**
  - Total Players with counting animation
  - Server Kills with live updates
  - Server Deaths tracking
  - K/D Ratio calculation
- **Loading overlay** with spinner for initial data fetch
- **Auto-refresh** every 30 seconds for real-time updates
- **Pop-in animations** for visual appeal

#### Dashboard Charts Implementation
**What Changed:** Added 4 interactive Chart.js visualizations to the dashboard
**Why:** Raw numbers are difficult to interpret. Visual charts provide immediate understanding of server trends and player activity patterns.

**Charts Added:**
1. **Top 5 Most Active Pilots**
   - Vertical bar chart with gradient fills
   - Bounce animation on load
   - Shows pilot names and visit counts
   
2. **Server Combat Statistics**
   - Doughnut chart comparing kills vs deaths
   - Percentage calculations in tooltips
   - Visual K/D ratio representation

3. **Top 3 Most Active Squadrons**
   - Horizontal bar chart with golden gradient
   - Aggregates squadron member visits
   - Only displays if squadron data available

4. **Player Activity Overview**
   - Line chart with filled area
   - Shows total registered vs active players
   - Smooth curve animation

**Technical Enhancements:**
- Created `get_server_stats.php` endpoint for aggregated statistics
- Gradient color schemes for visual hierarchy
- Responsive grid layout (2 columns desktop, 1 mobile)
- Enhanced tooltips with formatted numbers
- Axis titles for clarity on all charts

### 🛬 Carrier Operations Tracking

#### Carrier Trap Statistics
**What Changed:** Added carrier trap tracking to pilot statistics
**Why:** Naval aviators need recognition for successful carrier landings (traps). This metric is crucial for pilots flying carrier-based aircraft and adds depth to flight statistics.

**Implementation:**
- Reads trap data from `traps.json` file
- Displays "Carrier Traps" statistic between Landings and Crashes
- Enhanced Flight Statistics chart:
  - Dynamically shows separate slices for land vs carrier landings
  - Land landings (green), Carrier traps (blue)
  - Adjusts labels based on pilot data
- Maintains backward compatibility for pilots without traps

## [1.1.0] - 2025-07-28

### 🔍 Pilot Search Improvements

#### Partial Name Search Implementation
**What Changed:** Upgraded pilot search from exact match to partial name matching with multi-result selection
**Why:** Requiring exact pilot names made it difficult for users to find players, especially with complex usernames. Partial search allows finding pilots with just part of their name, improving user experience significantly.

**Key Features:**
- Case-insensitive partial name matching
- Multiple results display when search matches several pilots
- Clickable selection interface for choosing the correct pilot
- Results sorted by relevance (exact match first, then starts with, then contains)
- Limited to 20 results to prevent performance issues

#### Squadron Logo Display
**What Changed:** Added squadron logo display next to squadron names in pilot statistics
**Why:** Visual identification improves user engagement and squadron pride. Logos make it easier to identify squadron affiliations at a glance and create a more professional appearance.

**Implementation Details:**
- Automatically displays squadron logos when available
- 40x40px responsive sizing with rounded corners
- Graceful fallback to text-only display for squadrons without logos
- Integrated with existing squadron data fetching

### 📊 Data Visualization Features

#### Interactive Charts Integration
**What Changed:** Added Chart.js powered interactive charts to pilot statistics page
**Why:** Raw numbers are difficult to interpret quickly. Visual charts provide immediate understanding of pilot performance, making statistics more engaging and accessible to all users.

**Charts Added:**
1. **Combat Performance Chart**
   - Bar chart displaying kills and sorties
   - Green and blue color scheme for visual distinction
   - Responsive sizing and tooltips

2. **Flight Statistics Chart**
   - Doughnut chart showing flight outcomes
   - Visualizes successful landings, crashes, ejections, and in-flight status
   - Color-coded for quick understanding (green=success, red=crashes, orange=ejections)

3. **Aircraft Usage Chart**
   - Horizontal bar chart showing top 5 most used aircraft
   - Dynamic display only when aircraft data is available
   - Helps identify pilot preferences and specializations

**Technical Implementation:**
- Chart.js CDN integration with CSP updates
- Dark theme styling matching site design
- Responsive grid layout adapting to screen size
- Chart instances properly managed to prevent memory leaks
- Backend API enhanced to provide aircraft usage data

### 🆕 New Files Added

#### search_players.php
**Purpose:** New API endpoint for partial name search functionality
**Features:** 
- Supports partial name matching with case-insensitive search
- Returns up to 20 results sorted by relevance
- Includes security validation and rate limiting
- Properly sanitizes output to prevent XSS attacks

#### get_server_stats.php
**Purpose:** Server-wide statistics aggregation endpoint
**Features:**
- Calculates total players, kills, and deaths
- Identifies top 5 most active pilots by visits
- Aggregates top 3 squadrons by combined member activity
- Includes rate limiting and security validation
- Returns JSON formatted statistics for dashboard

## [1.1.0] - 2025-07-28

### 🔒 Security Enhancements

#### Environment Variable Configuration System
**What Changed:** Added support for `.env` files and environment variables for configuration
**Why:** Storing credentials in config files is a security risk. Environment variables keep sensitive data out of version control and allow different configurations per environment without code changes.

#### Secure FTP (FTPS) Implementation
**What Changed:** Upgraded FTP connections to use FTPS with TLS encryption by default
**Why:** Plain FTP transmits credentials and data in cleartext. FTPS encrypts all communication, protecting against credential theft and data interception on networks.

#### API Rate Limiting System
**What Changed:** Implemented session-based rate limiting for all API endpoints
**Why:** Without rate limiting, APIs are vulnerable to abuse and DoS attacks. Rate limiting prevents automated attacks and ensures fair resource usage across users.

#### XSS Protection Layer
**What Changed:** Added comprehensive input sanitization and output encoding across all user inputs
**Why:** User inputs displayed without sanitization can execute malicious JavaScript, stealing user data or performing unauthorized actions. XSS protection prevents these attacks.

#### HTTP Security Headers
**What Changed:** Added Content Security Policy, X-Frame-Options, and X-Content-Type-Options headers
**Why:** Modern browsers use security headers to prevent common attacks. CSP prevents XSS, X-Frame-Options prevents clickjacking, and X-Content-Type-Options prevents MIME confusion attacks.

#### Direct File Access Protection
**What Changed:** Added `.htaccess` rules to block direct access to JSON data files
**Why:** Direct file access could expose sensitive player data or allow unauthorized data harvesting. Access protection ensures data is only available through controlled API endpoints.

#### Input Validation Framework
**What Changed:** Implemented comprehensive input validation with character whitelisting
**Why:** Unvalidated inputs can contain malicious data or cause application errors. Validation ensures only expected data formats are processed, preventing injection attacks.

#### JSON Data Validation
**What Changed:** Added validation for NDJSON file structure and content before processing
**Why:** Malformed JSON can cause application crashes or security vulnerabilities. Validation ensures data integrity and prevents processing of corrupted or malicious data.

#### Secure Logging System
**What Changed:** Implemented structured logging with automatic rotation and sanitization
**Why:** Logs can contain sensitive information and grow infinitely. Secure logging prevents information leakage while maintaining operational visibility with manageable file sizes.

#### Path Traversal Protection
**What Changed:** Added path validation to prevent directory traversal attacks
**Why:** Unchecked file paths can allow attackers to access files outside intended directories. Path validation prevents unauthorized file system access.

### 🎨 User Interface Improvements

#### Trophy Box Centering Fix
**What Changed:** Fixed centering of trophy boxes on pilot credits page using flexbox wrapper
**Why:** Poor visual alignment creates unprofessional appearance and bad user experience. Proper centering improves visual hierarchy and makes the interface more polished.

#### Leaderboard Trophy Integration
**What Changed:** Added top 3 trophy display to leaderboard page matching the credits page design
**Why:** Inconsistent UI elements across pages create confusion and poor user experience. Unified trophy displays provide consistent recognition of top performers.

#### CSS Code Cleanup
**What Changed:** Removed duplicate CSS rules and consolidated styling definitions
**Why:** Duplicate CSS causes maintenance issues, larger file sizes, and potential styling conflicts. Clean CSS improves performance and maintainability.

#### Comprehensive Pilot Statistics Page
**What Changed:** Built complete pilot search functionality from scratch with dynamic data fetching
**Why:** Users need detailed individual player statistics for community management and engagement. The search page provides comprehensive player profiles with all relevant data in one place.

### 📚 Documentation Improvements

#### Professional README Overhaul
**What Changed:** Completely rewrote README from basic HTML list to comprehensive markdown documentation
**Why:** Poor documentation creates barriers to adoption and increases support burden. Professional documentation helps users understand, install, and maintain the system effectively.

**Key README Improvements:**
- Modern badges and visual elements for professional appearance
- Step-by-step installation guide for beginners
- Comprehensive feature breakdown with tables
- Security features documentation
- Troubleshooting section with common issues
- System architecture diagram
- API documentation and integration options

#### Enhanced File Structure Documentation
**What Changed:** Added detailed file structure overview with emojis and descriptions
**Why:** Users need to understand project organization for customization and troubleshooting. Clear structure documentation reduces confusion and support requests.

### ⚙️ Technical Infrastructure

#### Python Dependencies Management
**What Changed:** Added `requirements.txt` with python-dotenv dependency
**Why:** Manual dependency installation is error-prone and inconsistent across environments. Requirements files ensure all users have the same dependencies for reliable operation.

#### Git Configuration Improvements
**What Changed:** Added comprehensive `.gitignore` file to protect sensitive data
**Why:** Accidental commits of credentials or temporary files create security risks and repository pollution. Proper gitignore prevents sensitive data exposure.

#### Backward Compatibility Maintenance
**What Changed:** Ensured all security and feature updates work with existing configurations
**Why:** Breaking changes force users to reconfigure systems and can prevent updates. Backward compatibility allows seamless upgrades while improving security.

### 🔧 Development Workflow

#### SSH Key Management
**What Changed:** Added SSH agent configuration to `.bashrc` for automatic key loading
**Why:** Manual SSH key loading is tedious and error-prone. Automatic loading improves developer experience and reduces authentication failures.

#### Git History Sanitization
**What Changed:** Rewrote entire Git history to use consistent author identity and remove AI references
**Why:** Inconsistent commit authorship creates confusion about project ownership. Clean history improves professionalism and project credibility.

### 📊 Feature Enhancements

#### Dynamic Data Fetching
**What Changed:** Implemented real-time data fetching for pilot statistics with error handling
**Why:** Static data becomes stale and users expect real-time information. Dynamic fetching ensures users always see current statistics with graceful error handling.

#### Responsive Design Improvements
**What Changed:** Enhanced mobile compatibility and responsive layouts across all pages
**Why:** Users access websites from various devices. Responsive design ensures good user experience regardless of screen size or device type.

#### Search Functionality Enhancement
**What Changed:** Improved search with loading states and comprehensive error messages
**Why:** Poor search UX frustrates users and reduces engagement. Enhanced search provides clear feedback and helps users find information effectively.

---

## 🚀 Migration Guide

### For New Installations
1. Follow the updated README.md installation guide
2. Use the new `.env` configuration method for better security
3. Install Python dependencies with `pip install -r Stats-Uploader/requirements.txt`

### For Existing Installations
1. **No immediate action required** - all changes are backward compatible
2. **Recommended:** Migrate to `.env` configuration for improved security
3. **Optional:** Enable FTPS for encrypted file transfers
4. **Automatic:** Security protections are active immediately after update

### Configuration Updates
- **New:** Environment variable support (optional but recommended)
- **Enhanced:** FTP security with FTPS encryption
- **Improved:** Error logging with automatic rotation
- **Maintained:** All existing `config.ini` settings continue to work

---

## 🤝 Contributors

- **Socialoutcast** - Lead development and security enhancements
- **Penfold-88** - Original project creation and feature development

---

*This changelog focuses on functional changes and improvements. For security education and vulnerability explanations, see [FIXES.md](FIXES.md).*