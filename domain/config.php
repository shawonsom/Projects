

<?php
            $host = 'localhost';
            $dbname = 'test';
            $username = 'shawon';
            $password = 'c2140@SOM';
            $TABLE_NAME = 'ssl_list';  

            $total = $critical = $warning = $safe = 0;

            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $total = $pdo->query("SELECT COUNT(*) FROM ssl_list")->fetchColumn();

                // Critical: 0–6 days remaining (not expired)
                $critical = $pdo->query("SELECT COUNT(*) FROM ssl_list 
                    WHERE expire_date >= CURDATE() 
                    AND DATEDIFF(expire_date, CURDATE()) <= 6")->fetchColumn();

                // Warning: 7–15 days
                $warning = $pdo->query("SELECT COUNT(*) FROM ssl_list 
                    WHERE DATEDIFF(expire_date, CURDATE()) BETWEEN 7 AND 15")->fetchColumn();

                // Safe: 16+ days
                $safe = $pdo->query("SELECT COUNT(*) FROM ssl_list 
                    WHERE expire_date > DATE_ADD(CURDATE(), INTERVAL 15 DAY)")->fetchColumn();

            } catch(PDOException $e) {
                $total = $critical = $warning = $safe = 0;
                $db_error = $e->getMessage();
            }
            ?>