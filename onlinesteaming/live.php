<?php
session_start();

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "stream";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch only live videos
$live_videos = [];
$sql = "SELECT * FROM live_videos ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $live_videos[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Streaming & Recorded Live Videos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
        }
        #videoContainer {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        video {
            width: 600px;
            border-radius: 10px;
            background: black;
        }
        #controls {
            margin-top: 10px;
        }
        button {
            padding: 10px;
            margin: 5px;
            cursor: pointer;
        }
        .videoItem {
            width: 320px;
            display: inline-block;
            text-align: center;
            margin: 10px;
            background: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .videoItem h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <h1>Live Streaming & Recorded Live Videos</h1>

    <!-- Live Stream Section -->
    <h2>Start Live Streaming</h2>
    <div id="videoContainer">
        <video id="liveVideo" autoplay></video>
    </div>

    <div id="controls">
        <button id="startLive">Start Live</button>
        <button id="stopLive" disabled>Stop & Save</button>
    </div>

    <h2>Recorded Live Videos</h2>
    <div>
        <?php foreach ($live_videos as $video): ?>
            <div class="videoItem">
                <h3><?= htmlspecialchars($video['title']) ?> (Live)</h3>
                <video controls>
                    <source src="<?= htmlspecialchars($video['file_path']) ?>" type="video/mp4">
                </video>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        let mediaRecorder;
        let recordedChunks = [];

        document.getElementById('startLive').addEventListener('click', async function () {
            let stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            document.getElementById('liveVideo').srcObject = stream;

            recordedChunks = [];
            mediaRecorder = new MediaRecorder(stream);

            mediaRecorder.ondataavailable = (event) => recordedChunks.push(event.data);

            mediaRecorder.onstop = async function () {
                let blob = new Blob(recordedChunks, { type: 'video/mp4' });
                let file = new File([blob], "live_video.mp4", { type: 'video/mp4' });

                let formData = new FormData();
                formData.append('video', file);
                formData.append('isLive', 1); // Mark as live

                let response = await fetch('up.php', {
                    method: 'POST',
                    body: formData
                });

                let result = await response.text();
                alert(result);
                location.reload(); // Refresh to show new video
            };

            mediaRecorder.start();
            document.getElementById('startLive').disabled = true;
            document.getElementById('stopLive').disabled = false;
        });

        document.getElementById('stopLive').addEventListener('click', function () {
            mediaRecorder.stop();
            document.getElementById('startLive').disabled = false;
            document.getElementById('stopLive').disabled = true;
        });
    </script>

</body>
</html>
