<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "sql110.infinityfree.com";
$username = "if0_37153447";
$password = "phrz4sWrOhx";
$dbname = "if0_37153447_kyliebabe";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize $last_check_timestamp (you can adjust this to the correct value)
$last_check_timestamp = strtotime('2024-08-01 00:00:00'); // Replace with the actual last check time

// Fetch unique users with their actions and the most recent action time, sorted by the latest action time
$sql = "
    SELECT id, ip, country, city, MAX(action_datetime) AS last_action_time, COUNT(*) AS actions, os, user_agent, family, device
    FROM page_stats
    WHERE page='index.html'
    GROUP BY ip, country, city, os, user_agent, family, device
    ORDER BY last_action_time DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Statistics</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="#">Page Statistics</a>
    </div>

    <div class="container">
        <h1 class="header">Statistics for index.html</h1>

        <div class="btn-container">
            <a href="analytics2.php" class="btn">Refresh Data</a>
            <a href="#" class="btn" id="clearDataBtn">Clear Data</a>
        </div>

        <h2 class="header">Unique Users</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Country</th>
                        <th>City</th>
                        <th>Date/Time</th>
                        <th>Actions</th>
                        <th>More</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php 
                        $row_count = 0;
                        while ($row = $result->fetch_assoc()): 
                        $row_count++;
                        $last_action_time = strtotime($row['last_action_time']);
                        $is_incremented = $last_action_time > $last_check_timestamp ? 'true' : 'false';
                        ?>
                        <tr id="row-<?php echo $row['id']; ?>" class="<?php echo $row_count > 10 ? 'more-data' : ''; ?>" style="<?php echo $row_count > 10 ? 'display: none;' : ''; ?>" data-timestamp="<?php echo $last_action_time; ?>" data-incremented="<?php echo $is_incremented; ?>">
                            <td class="ip-cell">
                                <span><?php echo htmlspecialchars($row['ip']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($row['country']); ?></td>
                            <td><?php echo htmlspecialchars($row['city']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_action_time']); ?></td>
                            <td class="actions-cell"><?php echo $row['actions']; ?></td>
                            <td>
                                <button class="more-btn" 
                                    data-id="<?php echo htmlspecialchars($row['id'] ?? ''); ?>" 
                                    data-ip="<?php echo htmlspecialchars($row['ip'] ?? ''); ?>" 
                                    data-country="<?php echo htmlspecialchars($row['country'] ?? ''); ?>" 
                                    data-city="<?php echo htmlspecialchars($row['city'] ?? ''); ?>" 
                                    data-actions="<?php echo htmlspecialchars($row['actions'] ?? ''); ?>"
                                    data-os="<?php echo htmlspecialchars($row['os'] ?? 'Unknown OS'); ?>"
                                    data-user-agent="<?php echo htmlspecialchars($row['user_agent'] ?? 'Unknown User Agent'); ?>"
                                    data-family="<?php echo htmlspecialchars($row['family'] ?? 'Unknown Family'); ?>"
                                    data-device="<?php echo htmlspecialchars($row['device'] ?? 'Unknown Device'); ?>"
                                >More</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="show-btn">
            <a id="showMoreBtn" href="#">Show More</a>
            <a id="showLessBtn" href="#" style="display: none;">Show Less</a>
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>User Details</h2>
            <p><strong>IP Address:</strong> <span id="modalIp"></span></p>
            <p><strong>Country:</strong> <span id="modalCountry"></span></p>
            <p><strong>City:</strong> <span id="modalCity"></span></p>
            <p><strong>Actions:</strong> <span id="modalActions"></span></p>
            <p><strong>OS:</strong> <span id="modalOS"></span></p>
            <p><strong>Browser:</strong> <span id="modalBrowser"></span></p>
            <p><strong>Family:</strong> <span id="modalFamily"></span></p>
            <p><strong>Device:</strong> <span id="modalDevice"></span></p>
            <button id="deleteBtn" class="btn-delete">Delete</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let selectedRowId = null;

            // Event listener for the "More" button
            $('.more-btn').click(function() {
                // Fetch data attributes from the clicked button
                selectedRowId = $(this).data('id');
                const ip = $(this).data('ip') || 'Unknown IP';
                const country = $(this).data('country') || 'Unknown Country';
                const city = $(this).data('city') || 'Unknown City';
                const actions = $(this).data('actions') || 'Unknown Actions';
                const os = $(this).data('os') || 'Unknown OS';
                const userAgent = $(this).data('user-agent') || 'Unknown User Agent';
                const family = $(this).data('family') || 'Unknown Family';
                const device = $(this).data('device') || 'Unknown Device';

                // Populate modal with data
                $('#modalIp').text(ip);
                $('#modalCountry').text(country);
                $('#modalCity').text(city);
                $('#modalActions').text(actions);
                $('#modalDevice').text(device);
                $('#modalBrowser').text(userAgent);
                $('#modalFamily').text(family);
                $('#modalOS').text(os);

                // Show the modal
                $('#detailModal').css('display', 'block');
            });

            // Close the modal when the "close" button is clicked
            $('.close').click(function() {
                $('#detailModal').hide();
            });

            // Close the modal when clicking outside of it
            $(window).click(function(event) {
                if ($(event.target).is('#detailModal')) {
                    $('#detailModal').hide();
                }
            });

            // Delete button functionality
            $('#deleteBtn').click(function() {
                if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                    $.ajax({
                        url: 'delete_record.php', // Server-side delete script
                        method: 'POST',
                        data: { id: selectedRowId },
                        success: function(response) {
                            alert(response);
                            $(`#row-${selectedRowId}`).remove();
                            $('#detailModal').hide();
                        },
                        error: function() {
                            alert('An error occurred while trying to delete the record.');
                        }
                    });
                }
            });

            // Toggle visibility of additional rows
            $('#showMoreBtn').click(function() {
                toggleData();
            });

            $('#showLessBtn').click(function() {
                toggleData();
            });

            function toggleData() {
                const moreData = $('.more-data');
                const showMoreBtn = $('#showMoreBtn');
                const showLessBtn = $('#showLessBtn');

                moreData.each(function() {
                    if ($(this).css('display') === 'none') {
                        $(this).show();
                        showMoreBtn.hide();
                        showLessBtn.show();
                    } else {
                        $(this).hide();
                        showMoreBtn.show();
                        showLessBtn.hide();
                    }
                });
            }

            // Highlight new rows
            function highlightRows() {
                const highlightedRows = JSON.parse(localStorage.getItem('highlightedRows')) || {};

                $('tr[data-incremented="true"]').each(function() {
                    const rowId = $(this).attr('id').replace('row-', '');
                    const currentActions = parseInt($(this).find('.actions-cell').text(), 10);

                    if (!highlightedRows[rowId] || highlightedRows[rowId] !== currentActions) {
                        $(this).addClass('highlight'); // Add highlight class for visual effect
                        highlightedRows[rowId] = currentActions;

                        setTimeout(() => {
                            $(this).removeClass('highlight'); // Remove highlight class after 10 seconds
                        }, 10000); // 10 seconds
                    }
                });

                localStorage.setItem('highlightedRows', JSON.stringify(highlightedRows));
            }

            highlightRows();
        });
    </script>
</body>
</html>
