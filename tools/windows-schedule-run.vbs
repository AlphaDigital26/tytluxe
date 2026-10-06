' Runs Laravel's scheduler once, with no console window.
' Registered in Windows Task Scheduler to fire every minute — see README
' ("required server setup"). Paths are resolved relative to this file.
Set fso = CreateObject("Scripting.FileSystemObject")
projectDir = fso.GetParentFolderName(fso.GetParentFolderName(WScript.ScriptFullName))
php = "C:\xampp\php\php.exe"
Set shell = CreateObject("WScript.Shell")
shell.CurrentDirectory = projectDir
shell.Run """" & php & """ artisan schedule:run", 0, False
