To exit an Active Directory (AD) domain in Windows 11, you essentially "unjoin" the computer by moving it into a Workgroup.

**Important Note:** Before you begin, ensure you have the credentials for a **local administrator account**. Once you leave the domain, you will no longer be able to log in with your domain username and password.

---

## Method 1: Using Windows Settings (Simplest)

1. Open **Settings** (Win + I).
2. Go to **Accounts** > **Access work or school**.
3. Locate the domain connection you want to remove and click the **Disconnect** button.
4. Confirm the prompt by clicking **Yes**, then click **Disconnect** again.
5. You will be prompted to **Restart** your PC to complete the process.

---

## Method 2: Using System Properties (Classic Method)

This is the traditional way to change domain membership and is often more reliable if the Settings app is restricted.

1. Press `Win + R`, type `sysdm.cpl`, and hit **Enter**.
2. On the **Computer Name** tab, click the **Change...** button.
3. Under the "Member of" section, select **Workgroup**.
4. Type a name for the workgroup (e.g., `WORKGROUP`) and click **OK**.
5. A prompt will appear asking for **Domain Administrator** credentials to authorize the removal.
6. Click **OK** through the "Welcome to the Workgroup" and "Restart" prompts, then reboot your machine.

---

## Method 3: Using PowerShell (Advanced)

If you prefer the command line or need to script this for multiple machines, use an elevated PowerShell window (Run as Administrator).

To remove the computer and restart immediately, run:

```powershell
Remove-Computer -UnjoinDomainCredential (Get-Credential) -Restart

```
To rejoin a Windows 11 computer to an Active Directory (AD) domain, you follow the same path as joining for the first time.

---

## Method 1: Using Windows Settings

1. Go to **Settings > Accounts > Access work or school**.
2. Click the **Connect** button.
3. Do **not** enter an email address. Instead, look for the link at the bottom that says **"Join this device to a local Active Directory domain"**.
4. Enter the **Domain Name** (e.g., `company.local`) and click **Next**.
5. Enter the **Domain Administrator** credentials when prompted.
6. Restart your computer.

---

## Method 2: Using System Properties (Classic)

This is often more reliable if the Settings app link is missing.

1. Press `Win + R`, type `sysdm.cpl`, and hit **Enter**.
2. On the **Computer Name** tab, click **Change...**.
3. Select the **Domain** radio button and type your domain name.
4. Click **OK**. You will be prompted for domain credentials.
5. After the "Welcome to the [Domain] domain" message appears, click **OK** and **Restart**.

---

## Method 3: Using PowerShell

This is the fastest method and provides clear error messages if the join fails.

1. Open **PowerShell** as an Administrator.
2. Run the following command:
```powershell
Add-Computer -DomainName "yourdomain.com" -Restart

```


3. A login box will appear; enter your domain administrator credentials. The PC will automatically reboot and be joined to the domain.

