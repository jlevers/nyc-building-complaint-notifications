<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

const API_URL = 'https://data.cityofnewyork.us/resource/eabe-havv.json';
const COMPLAINTS_FILE = 'complaints.txt';

// Using cURL because Sendgrid's PHP library is broken (autoloading doesn't work)
function sendEmail($complaintIds): void
{
    $url = 'https://api.sendgrid.com/';
    $apiKey = $_ENV['SENDGRID_API_KEY'];
    $message = "New complaints on BIN {$_ENV['NYC_BUILDING_ID']}: " . implode(', ', $complaintIds) . '. View them <a href="' . urlByBin() . '">here</a>.';

    $params = array(
        'to'        => explode(',', $_ENV['TO_EMAILS']),
        'from'      => $_ENV['FROM_EMAIL'],
        'fromname'  => 'NYC Building Alerts',
        'subject'   => 'New NYC building complaints',
        'html'      => $message,
    );

    $request =  $url.'api/mail.send.json';

    // Generate curl request
    $session = curl_init($request);
    // Tell PHP not to use SSLv3 (instead opting for TLS)
    curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $apiKey));
    // Tell curl to use HTTP POST
    curl_setopt ($session, CURLOPT_POST, true);
    // Tell curl that this is the body of the POST
    curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
    // Tell curl not to return headers, but do return the response
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

    // obtain response
    curl_exec($session);
    curl_close($session);
}

function urlByBin(): string
{
    return API_URL . '?bin=' . $_ENV['NYC_BUILDING_ID'];
}

function main(): void
{
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $complaints = json_decode(file_get_contents(urlByBin()), true);

    $complaintsFile = fopen(COMPLAINTS_FILE, 'a');

    $complaintIds = array_column($complaints, 'complaint_number');
    $knownComplaintIds = explode("\n", file_get_contents(COMPLAINTS_FILE));
    array_pop($knownComplaintIds);  // Remove trailing newline
    $unknownComplaintIds = array_diff($complaintIds, $knownComplaintIds);

    $forEmail = [];
    foreach ($complaints as $complaint) {
        $complaintId = $complaint['complaint_number'];
        if (!in_array($complaintId, $unknownComplaintIds)) {
            continue;
        }

        $forEmail[] = $complaintId;
        fputs($complaintsFile, $complaintId . "\n");
    }

    fclose($complaintsFile);

    if (count($forEmail) > 0) {
        sendEmail($forEmail);
    }
}

main();
