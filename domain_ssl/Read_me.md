# Domain SSL Certificate Monitor

A Bash script that automatically monitors SSL certificate expiration dates for domains and sends email alerts when certificates are nearing expiration.

## 📋 Overview

This tool helps system administrators proactively manage SSL certificates by monitoring their expiration dates and sending early warning notifications via email. This prevents unexpected certificate expirations that can cause service disruptions.

## ✨ Features

- **Automated SSL Expiry Monitoring**: Checks SSL certificate expiration dates for specified domains
- **Configurable Alert Thresholds**: Set custom warning periods (30 days, 15 days, 7 days, 1 day)
- **Email Notifications**: Sends formatted email alerts with certificate details
- **Multi-Domain Support**: Monitor multiple domains simultaneously
- **Cron Job Ready**: Designed for automated scheduled execution
- **Email Formatting**: Professional-looking email alerts with proper formatting

## 🛠️ Requirements

- `postfix ` and `mailutils` - For sending email notifications
- `cron` - For scheduling automated checks

## 📁 Project Structure

```
domain_ssl/
├── mail_script.sh          # Main monitoring script
├── db_dump.sql             # Databases Dump
└── README.md               # This file
```

## ⚙️ Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/shahriarprg/small_projects.git
   cd small_projects/domain_ssl
   ```

2. **Make scripts executable**:
   ```bash
   chmod +x mail_script.sh
   ```

3. **Restore Databases settings**:
  Restore mysql databases 
   ```bash
   mysql -u username -p Domain_SSL < db_dump.sql
   ```

4. **Configure domain list**:
   Add your domains in Databases in `ssl_list` tables:
   ```bash
   insert  into `ssl_list`(`domain_name`,`expire_date`,`mail_to`) values ('yourdomainname.com','2025-12-01','shawonsom@gmail.com');
   ```

4. **Configure email settings**:
   Update the email recipient in `mail_script.sh`:
   ```bash
   RECIPIENT="your-email@company.com"
   ```

## 🔧 Configuration
   ### 1️⃣ Install mailx
   Ubuntu/Debian:
  ```bash 
    sudo apt update
   sudo apt install mailutils -y
   ```
  mailutils provides /usr/bin/mailx.
  CentOS/RHEL/Fedora:
  ```bash
    sudo yum install mailx -y
   # or for dnf-based systems
   sudo dnf install mailx -y
  ```
  ### 2️⃣ Test it
   After installing, test sending a simple email:
  ```bash
   echo "Test email body" | mailx -s "Test Subject" your_email@example.com
   ```
   If you get the email, your script should now be able to send alerts.

   ### 3️⃣ Optional: Configure a Mail Transfer Agent (MTA)
   mailx needs an MTA (like postfix or ssmtp) to actually deliver emails. For testing, you can install postfix in “Internet Site” mode:
   ```bash
   sudo apt install postfix -y
   ```

During setup, enter your server domain, and it will relay outgoing emails. Use an SMTP relay
Use a legitimate SMTP server like Gmail, Office 365, or your hosting provider to send emails.
Postfix will authenticate to that relay, so SPF/DKIM checks pass.
Example for Gmail SMTP in `/etc/postfix/main.cf`:
```bash
relayhost = [smtp.gmail.com]:587
smtp_sasl_auth_enable = yes
smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd
smtp_sasl_security_options = noanonymous
smtp_tls_security_level = encrypt
smtp_tls_CAfile = /etc/ssl/certs/ca-certificates.crt
```
And ```/etc/postfix/sasl_passwd```:
```bash
[smtp.gmail.com]:587 yourgmail@gmail.com:yourapppassword
```
Then run:
```bash
sudo postmap /etc/postfix/sasl_passwd
sudo systemctl restart postfix
```
Gmail requires an App Password if 2FA is enabled.

## 🚀 Usage

### Manual Execution
```bash
./mail_script.sh
```

### Automated Monitoring with Cron
Set up daily automated checks:
```bash
crontab -e
```
Add manually the script location (here daily at 12:15 PM script will run)\
```bash
15 12 * * * bash /path/to/domain_ssl/mail_script.sh
```

## 📧 Email Alert Format

Recipients will receive formatted email alerts:

```
The SSL certificate for the domain '*.btcl.com.bd' is expiring soon.

Expiration Date: 2025-11-28
Days Remaining: 4
Action Required: Please renew the certificate immediately.
 
 
 Don't reply this is a system Mail

```

## 🔍 How It Works

1. **Domain Reading**: Script reads domains from `domains.list`
2. **SSL Certificate Check**: Uses OpenSSL to query certificate expiration dates
3. **Date Calculation**: Compares current date with certificate expiry
4. **Alert Logic**: Triggers emails based on configured threshold days
5. **Notification**: Sends formatted HTML email alerts


## 🤝 Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for:

- New features
- Bug fixes
- Documentation improvements
- Additional monitoring capabilities

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## ⚠️ Disclaimer

This tool is provided as-is for monitoring purposes. Always verify SSL certificate status through multiple channels and maintain proper certificate management procedures.

## 📞 Support

For issues and questions:
1. Check the troubleshooting section above
2. Review script comments for configuration options
3. Open an issue on GitHub
