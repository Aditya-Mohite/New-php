<?php
include 'connect.php';

// Fetch verified email addresses
$sql = "SELECT DISTINCT email FROM user WHERE verify='1'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Collect email addresses
    $emails = [];
    while ($row = $result->fetch_assoc()) {
        $emails[] = $row["email"];
    }

    // Get GitHub timeline data
    $myXMLData = file_get_contents('https://github.com/timeline');
    $xml = simplexml_load_string($myXMLData) or die("Error: Cannot create object");
    $maxLen = 10;
    $timelineEntries = '';

    for ($count = 0; ($count < $maxLen && $count < count($xml->entry)); $count++) {
        $entry = $xml->entry[$count];
        $title = $entry->title;
        $author = $entry->author->name;
        $link = $entry->link['href="https://github.com/timeline"'];
        $published = $entry->published;
        $content = $entry->content;

        $timelineEntries .= "<h3>{$title}</h3>";
        $timelineEntries .= "<p><strong>Author:</strong> {$author}</p>";
        $timelineEntries .= "<p><strong>Link:</strong> <a href='{$link}'>View on GitHub</a></p>";
        $timelineEntries .= "<p><strong>Published:</strong> {$published}</p>";
        $timelineEntries .= "<p>{$content}</p><hr>";
    }

    // Send email to each user
    $subject = 'Latest GitHub Updates';
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: your-email@example.com' . "\r\n";

    foreach ($emails as $email) {
        if (mail($email, $subject, $timelineEntries, $headers)) {
            echo "Message has been sent to {$email}<br>";
        } else {
            echo "Failed to send message to {$email}<br>";
        }
    }
} else {
    echo "0 results";
}
?>
