# NYC Building Complaint Notifier

This is a simple script that will send you an email when a new complaint is filed against a building in NYC. It uses the [NYC Open Data API](https://dev.socrata.com/foundry/data.cityofnewyork.us/hy4q-igkk) to get the data and [SendGrid](https://sendgrid.com/) to send the email.

To use this program, you'll need a Sendgrid API key and the NYC building ID you want to monitor. You can find the building ID by searching for the building by address on the [NYC Building Information Search website](https://a810-bisweb.nyc.gov/bisweb/bispi00.jsp), and grabbing the BIN # value you find.

Once you have all that, you can set up the program like so:

```bash
git clone git@github.com:jlevers/nyc-building-complaint-notifications.git
composer install
cp .env.example .env
```

Edit the `.env` file to add your Sendgrid API key and the building ID you want to monitor, as well as the email addresses you want to send the notifications from and to.

Then just run the script:

```bash
php index.php
```
