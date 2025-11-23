#!/bin/bash
# --- SSL Expiration Check Script ---

# --- 1. CONFIGURATION ---

# Database configuration
DB_HOST="localhost"
DB_NAME="Domain_SSL"
DB_USER="DB_user"
DB_PASS="your_DB_pass"

# Email configuration
MAIL_CMD="/usr/bin/mailx"                   # Path to mail utility

# Threshold for alerts (in days)
EXPIRATION_THRESHOLD=10

# Temporary file to store SQL query results
TEMP_FILE=$(mktemp)

# --- 2. SQL QUERY ---
SQL_QUERY="SELECT domain_name, expire_date, mail_to FROM ssl_list"

# --- 3. EMAIL FUNCTION ---
send_ssl_alert() {
    local DOMAIN_NAME="$1"
    local EXPIRE_DATE="$2"
    local DIFF_DAYS="$3"
    local RECIPIENT_LIST="$4"

    # Normalize email separators: commas, semicolons, or spaces -> single space
    local RECIPIENTS=$(echo "$RECIPIENT_LIST" | tr ',;' ' ' | xargs)

    # Skip if no valid recipients
    if [ -z "$RECIPIENTS" ]; then
        echo "  - WARNING: No valid recipients found. Skipping email."
        return 1
    fi

    local EMAIL_SUBJECT="URGENT: SSL for $DOMAIN_NAME Expires in $DIFF_DAYS Days"
    
    
    local EMAIL_BODY="The SSL certificate for the domain *.$DOMAIN_NAME is expiring soon.\n\n"
    EMAIL_BODY+="Expiration Date: $EXPIRE_DATE\n"
    EMAIL_BODY+="Days Remaining: $DIFF_DAYS\n"
    EMAIL_BODY+="Action Required: Please renew the certificate before it expires."
    EMAIL_BODY+="\n \n \n Don't reply this is a system Mail"

    # Check if mail command exists
    if ! command -v "$MAIL_CMD" &>/dev/null; then
        echo "  - ERROR: Mail utility ($MAIL_CMD) not found. Cannot send email."
        return 1
    fi

    # Send email
    echo -e "$EMAIL_BODY" | "$MAIL_CMD" -s "$EMAIL_SUBJECT" -r "$SENDER_EMAIL" $RECIPIENTS

    if [ $? -eq 0 ]; then
        echo "  -> Email sent successfully to: $RECIPIENT_LIST"
    else
        echo "  -> ERROR: Failed to send email via $MAIL_CMD. Check your MTA configuration."
        return 1
    fi
}

# --- 4. MAIN SCRIPT LOGIC ---

echo "--- SSL Expiration Check Script ---"
echo "Database: $DB_NAME on $DB_HOST. Threshold: $EXPIRATION_THRESHOLD days."

# Execute SQL query
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "$SQL_QUERY" > "$TEMP_FILE"

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to connect to MariaDB or execute query."
    rm -f "$TEMP_FILE"
    exit 1
fi

FOUND_EXPIRING=0

# Read query results line by line
while IFS=$'\t' read -r DOMAIN_NAME EXPIRE_DATE INFORM_CELL; do
    # Skip if domain or expire date is empty
    if [ -z "$DOMAIN_NAME" ] || [ -z "$EXPIRE_DATE" ]; then
        continue
    fi

    # Calculate difference in days
    TODAY_TS=$(date +%s)
    EXPIRE_TS=$(date -d "$EXPIRE_DATE" +%s 2>/dev/null)
    if [ $? -ne 0 ]; then
        echo "WARNING: Invalid date format for $DOMAIN_NAME ($EXPIRE_DATE). Skipping."
        continue
    fi

    DIFF_DAYS=$(( (EXPIRE_TS - TODAY_TS) / 86400 ))

    # Alert if certificate expires within threshold
    if [ "$DIFF_DAYS" -ge 0 ] && [ "$DIFF_DAYS" -le "$EXPIRATION_THRESHOLD" ]; then
        FOUND_EXPIRING=1

        echo -e "\nALERT: **$DOMAIN_NAME**"
        echo "  - Expires: $EXPIRE_DATE"
        echo "  - Days Remaining: $DIFF_DAYS"

        # Send email alert if recipients exist
        if [ -n "$INFORM_CELL" ]; then
            send_ssl_alert "$DOMAIN_NAME" "$EXPIRE_DATE" "$DIFF_DAYS" "$INFORM_CELL"
        else
            echo "  - WARNING: mail_to is empty. No email sent."
        fi
    fi
done < "$TEMP_FILE"

# Cleanup
rm -f "$TEMP_FILE"

if [ "$FOUND_EXPIRING" -eq 0 ]; then
    echo -e "\nNo SSL certificates found expiring within the next $EXPIRATION_THRESHOLD days."
fi

echo -e "\nScript finished."
