# CumulusMX Integration with Website Folder

The CumulusMX server integrates with the website folder through a secure HTTP communication mechanism. Here's how they entwine:

## 1. **Authentication & Security**
- Both systems use a shared secret (`6ee0b863-617d-447d-bf9d-2208f8b4c86d`) for authentication
- HMAC SHA256 signatures are used to validate data integrity during transfers

## 2. **File Transfer Mechanism**
- CumulusMX uses `HttpClient` in `HttpFiles.cs` to communicate with the PHP upload script
- The PHP script (`upload.php`) handles file operations with security checks:
  - Path restriction to prevent directory traversal attacks
  - File permission validation
  - Signature verification before processing

## 3. **Configuration Integration**
- App.config contains `PhpMaxConnections` setting (currently set to 3)
- This controls how many simultaneous connections CumulusMX can make to the PHP script

## 4. **Data Flow**
- Files are transferred using HTTP POST with custom headers
- The PHP script handles both append and replace operations based on the `ACTION` header
- Timestamp validation ensures data freshness (20-second window)

## 5. **Error Handling**
- Both systems implement detailed error logging
- CumulusMX has built-in retry logic for failed transfers
- PHP script returns specific HTTP status codes for different error conditions

This integration allows CumulusMX to securely transfer weather data files to the website's file system while maintaining strict security controls and validation mechanisms.