@echo off
cd /d "%~dp0"
title DMPOS Local Print Agent
php print-agent.php
echo.
echo The print agent stopped. Review the error above and print-agent.log.
pause
