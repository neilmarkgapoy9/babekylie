<?php
// Database connection details
$servername = "sql110.infinityfree.com";
$username = "if0_37153447";
$password = "phrz4sWrOhx";
$dbname = "if0_37153447_kyliebabe";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch unique users with their actions and the most recent action time, sorted by the latest action time
$sql = "
    SELECT id, ip, country, city, MAX(action_datetime) AS last_action_time, COUNT(*) AS actions
    FROM page_stats
    WHERE page='index.html'
    GROUP BY ip, country, city
    ORDER BY last_action_time DESC
";

$result = $conn->query($sql);

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
    <link rel="stylesheet" href="style.css">
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
  .ip-cell {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .search-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #007bff;
            font-size: 1.2em;
            transition: color 0.3s ease;
        }

        .search-btn:hover {
            color: #0056b3;
        }

        .btn-container {
            text-align: center;
            margin-bottom: 20px;
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
            <a href="view_stats.php" class="btn">Refresh Data</a>
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
                        <th class="actions-header">Actions</th>
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
                                <button class="search-btn" data-ip="<?php echo htmlspecialchars($row['ip']); ?>" title="Search IP">
                                    <i class="fas fa-search"></i>
                                </button>
                            </td>
                            <td><?php echo htmlspecialchars($row['country']); ?></td>
                            <td><?php echo htmlspecialchars($row['city']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_action_time']); ?></td>
                            <td class="actions-cell"><?php echo $row['actions']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No data available</td>
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

    <script>
$(document).ready(function() {
    $('#clearDataBtn').click(function(event) {
        event.preventDefault();
        if (confirm('Are you sure you want to clear all data? This action cannot be undone.')) {
            $.ajax({
                url: 'clear_data.php',
                method: 'POST',
                success: function(response) {
                    alert(response);
                    localStorage.removeItem('highlightedRows');
                    location.reload();
                },
                error: function() {
                    alert('An error occurred while trying to clear data.');
                }
            });
        }
    });

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

    // Add event listener for the search buttons
    $('.search-btn').click(function() {
        const ip = $(this).data('ip');
        if (ip) {
            const searchUrl = `https://whatismyipaddress.com/ip/${ip}`;
            window.open(searchUrl, '_blank');
        }
    });
});

</script>




</body>
</html>
