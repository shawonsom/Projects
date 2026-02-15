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

* **What happens:** This will pop up a login box for your domain credentials, then automatically unjoin the PC and reboot it.

---

### What to do after exiting

* **Log in locally:** Use your local username. If you aren't sure of the name, try entering `.\username` at the login screen.
* **Clean up AD:** If you are an IT admin, remember to delete the computer object from **Active Directory Users and Computers** on the Server to keep the directory clean.
