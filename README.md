# 🎵 Music Library

A modern PHP music library management system with album organization, file uploads, and responsive design.

## ✨ Features

- **Music Upload**: Upload MP3, WAV, OGG audio files
- **Album Management**: Create albums and organize songs
- **Search & Pagination**: Find music quickly with AJAX search
- **Admin Panel**: Secure admin interface for music management
- **Responsive Design**: Works on desktop and mobile
- **Audio Player**: Built-in HTML5 audio controls

## 🚀 Installation

### Requirements
- PHP 7.4+
- MySQL 5.7+
- Apache with mod_rewrite
- 50MB+ upload limit

### Quick Setup

1. **Download & Extract**
   ```bash
   git clone https://github.com/mrvh-ir/mrvh-music
   cd mrvh-music
   ```

2. **Database Configuration**
   - Edit `database/database.php` with your MySQL credentials:
   ```php
   private $host = 'localhost';
   private $user = 'mysqlUser';
   private $password = 'mysqlPass';
   private $database = 'dbName';
   ```

3. **Run Migration**
   ```bash
   # Visit in browser to setup database
   http://yourdomain.com/migrate.php
   ```

4. **Set Permissions**
   ```bash
   chmod 755 musics/
   chmod 644 .htaccess
   ```

### Default Login
- **Username**: `admin`
- **Password**: `password`

## 📁 Project Structure

```
musicMrvh/
├── index.php          # Main music library
├── admin.php          # Admin panel
├── albums.php         # Albums view
├── migrate.php        # Database setup
├── .htaccess          # Apache configuration
├── musics/            # Uploaded music files
├── database/
│   ├── database.php   # Database class
│   ├── migration/     # SQL schema files
│   └── seed/          # Default data
└── src/
    ├── auth/          # Login/logout
    ├── music/         # Music management
    ├── album/         # Album management
    └── abstract.php   # Database connection
```

## 🎯 Usage

### For Users
1. Visit the homepage to browse music
2. Use search to find specific songs/artists/albums
3. Click albums to view songs within them
4. Use pagination to navigate large collections

### For Admins
1. Login at `/src/auth/login.php`
2. Upload music files with artist/album info
3. Create new albums
4. Delete unwanted music
5. Manage the entire library

## 🔧 Configuration

### Upload Limits
Edit `.htaccess` to change file size limits:
```apache
php_value upload_max_filesize 100M
php_value post_max_size 100M
```

### Database Settings
Update `database/database.php` for different environments:
```php
private $host = 'your_host';
private $port = '3306';
private $database = 'your_db_name';
```

## 🛠️ Troubleshooting

### Common Issues

**Database Connection Error**
- Check MySQL credentials in `database/database.php`
- Ensure MySQL service is running
- Verify database exists

**File Upload Fails**
- Check `musics/` directory permissions (755)
- Increase PHP upload limits in `.htaccess`
- Verify file type is supported (MP3, WAV, OGG)

**Login Issues**
- Default credentials: admin/password
- Check session configuration in PHP
- Clear browser cookies

### File Permissions
```bash
# Set correct permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 musics/
```

## 🔒 Security

- SQL injection protection with prepared statements
- XSS prevention with HTML escaping
- File type validation for uploads
- Session-based authentication
- Protected SQL files via .htaccess

## 📱 Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 🎨 Technology Stack

- **Backend**: PHP 7.4+, MySQL
- **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript
- **Audio**: HTML5 Audio API
- **Upload**: AJAX with FormData
- **Server**: Apache with mod_rewrite

## 📄 License

Open source - Apache 2.0 - feel free to modify and distribute.

---

**Need help?** Check the troubleshooting section or review the code comments for guidance.