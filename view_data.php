<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ดูข้อมูลตำแหน่ง</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>📊 ข้อมูลตำแหน่งทั้งหมด</h1>
    
    <?php
    $csv_file = 'locations.csv';
    
    if (file_exists($csv_file)) {
        echo "<p>พบข้อมูล " . count(file($csv_file)) - 1 . " รายการ</p>";
        
        echo "<table>";
        echo "<tr><th>เวลา</th><th>ละติจูด</th><th>ลองจิจูด</th><th>ความแม่นยำ</th><th>IP</th><th>ดูแผนที่</th></tr>";
        
        $file = fopen($csv_file, 'r');
        $first = true;
        
        while (($data = fgetcsv($file)) !== FALSE) {
            if ($first) {
                $first = false;
                continue; // ข้าม header
            }
            
            echo "<tr>";
            echo "<td>" . date('d/m/Y H:i:s', strtotime($data[0])) . "</td>";
            echo "<td>{$data[1]}</td>";
            echo "<td>{$data[2]}</td>";
            echo "<td>{$data[3]} m</td>";
            echo "<td>{$data[4]}</td>";
            echo "<td><a href='https://maps.google.com/?q={$data[1]},{$data[2]}' target='_blank'>🗺️ ดูแผนที่</a></td>";
            echo "</tr>";
        }
        
        fclose($file);
        echo "</table>";
    } else {
        echo "<p>ยังไม่มีข้อมูล</p>";
    }
    ?>
    
    <br>
    <a href="index.html">← กลับไปหน้าแรก</a>
</body>
</html>