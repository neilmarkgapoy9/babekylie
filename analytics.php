<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

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

// Fetch top countries
$top_countries_sql = "
    SELECT country, COUNT(*) AS count
    FROM page_stats
    WHERE page='index.html'
    GROUP BY country
    ORDER BY count DESC
    LIMIT 10
";

$top_countries_result = $conn->query($top_countries_sql);

if (!$top_countries_result) {
    die("Query failed: " . $conn->error);
}

// Fetch device statistics
$device_stats_sql = "
    SELECT device, COUNT(*) AS count
    FROM page_stats
    WHERE page='index.html'
    GROUP BY device
";

$device_stats_result = $conn->query($device_stats_sql);

if (!$device_stats_result) {
    die("Query failed: " . $conn->error);
}

$device_stats = [];
while ($row = $device_stats_result->fetch_assoc()) {
    $device_stats[$row['device']] = (int)$row['count'];
}

// Encode device stats as JSON
$device_stats_json = json_encode($device_stats);

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Responsive Collapsibles</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      width: 90%;
      max-width: 800px;
      margin: auto;
      padding: 20px;
      background-color: #f4f4f9;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

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

    .collapsible {
      background-color: #007bff;
      color: white;
      cursor: pointer;
      padding: 15px;
      width: 100%;
      border: none;
      text-align: left;
      outline: none;
      font-size: 16px;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 5px;
      transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;
      position: relative;
    }

    .collapsible:hover {
      background-color: #0056b3;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      transform: scale(1.02);
    }

    .active, .collapsible:active {
      background-color: #0056b3;
    }

    .collapsible:after {
      content: '\f067'; /* FontAwesome plus icon */
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      color: white;
      float: right;
      margin-left: 10px;
      font-size: 18px;
    }

    .active:after {
      content: '\f068'; /* FontAwesome minus icon */
    }

    .content {
      max-height: 0;
      padding:10px;
      overflow: hidden;
      transition: max-height 0.5s ease, opacity 0.5s ease;
      background-color: #ffffff;
      border-radius: 0 0 5px 5px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      opacity: 0;
    }

    .content.show {
      opacity: 1;
    }

    @media (max-width: 600px) {
      .collapsible {
        font-size: 14px;

      }
      body {
          width:100%;
      }
    }
    .table-container {
    overflow-x: auto; /* Allows horizontal scrolling for small screens */
    margin-top: 20px;
}

table {
    width: 100%; /* Ensures the table takes up full width of the container */
    border-collapse: collapse;
    margin: 0 auto;
}

th, td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

tr:hover {
    background-color: #ddd;
}

.btn-container {
    margin-bottom: 20px;
}

.btn {
    display: inline-block;
    padding: 10px 15px;
    margin: 0 5px;
    border-radius: 5px;
    color: #fff;
    background-color: #007bff;
    text-decoration: none;
    text-align: center;
}

.btn:hover {
    background-color: #0056b3;
}

.show-btn a {
    display: inline-block;
    margin: 10px;
    color: #007bff;
    text-decoration: none;
}

.show-btn a:hover {
    text-decoration: underline;
}

.content .row {
  display: flex;
  flex-wrap: wrap;
}

.col-md-6 {
  flex: 1;
  padding: 15px;
}

.list-group-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

#deviceChart {
  max-width: 100%;
  height: auto;
}
.search-icon {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: #007bff;
    margin-left: 10px;
}

.search-icon i {
    font-size: 16px; /* Adjust the size as needed */
}

/* Include Font Awesome library if not already included */


  </style>
</head>
<body>

  <section>
  <div class="container" align="center" >
  <h2>Admin Panel</h2>

    <!-- Logout Icon -->
    <a href="logout.php"  class="logout-icon" title="Logout">
      <i class="fas fa-sign-out-alt"></i>
    </a>
<hr>
    <!-- Your existing HTML content -->

  </div>
    
    
    <button class="collapsible" data-id="statistics">
      <i class="fas fa-chart-line"></i> Statistics
    </button>
    <div class="content">
      
      




        <div class="btn-container" style="text-align:center;">
            <a href="analytics.php" class="btn">Refresh Data</a>
            <a href="#" class="btn" id="clearDataBtn">Clear Data</a>
        </div>

    
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
  

   <!-- Modal Structure -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>User Details</h2>
        <p>
            <strong>IP Address:</strong>
            <span id="modalIp"></span>
            <button id="searchIpBtn" class="search-icon">
                <i class="fa fa-search"></i> <!-- Font Awesome search icon -->
            </button>
        </p>
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

     // JavaScript code to handle "Clear Data" button click
    $('#clearDataBtn').click(function() {
        if (confirm('Are you sure you want to clear all data? This action cannot be undone.')) {
            $.ajax({
                url: 'clear_data.php',
                method: 'POST',
                success: function(response) {
                    alert(response);
                    location.reload(); // Refresh the page to reflect the changes
                },
                error: function() {
                    alert('An error occurred while trying to clear the data.');
                }
            });
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

    // Open IP search URL on search icon click
    $('#searchIpBtn').click(function() {
        const ip = $('#modalIp').text();
        if (ip && ip !== 'Unknown IP') {
            const searchUrl = `https://whatismyipaddress.com/ip/${encodeURIComponent(ip)}`;
            window.open(searchUrl, '_blank');
        } else {
            alert('IP Address is not available.');
        }
    });
});

    </script>




















    </div>
    <button class="collapsible" data-id="countries">
  <i class="fas fa-globe-americas"></i> Top Countries
</button>
<div class="content">
  <table>
    <thead>
      <tr>
        <th>Country</th>
        <th>Number of Visits</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($top_countries_result->num_rows > 0): ?>
        <?php while ($country_row = $top_countries_result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($country_row['country']); ?></td>
            <td><?php echo htmlspecialchars($country_row['count']); ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="2">No data available</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

   <button class="collapsible" data-id="devices">
  <i class="fas fa-mobile-alt"></i> Devices
</button>
<div class="content">
  <div class="row">
    <div class="col-md-6">
      <h3>Device List</h3>
      <ul id="deviceList" class="list-group">
        <?php
        foreach ($device_stats as $device => $count) {
            echo "<li class='list-group-item'>" . htmlspecialchars($device) . ": " . $count . "</li>";
        }
        ?>
      </ul>
    </div>
    <div class="col-md-6">
      <h3>Device Distribution</h3>
      <canvas id="deviceChart" width="400" height="400"></canvas>
    </div>
  </div>
</div>



  </section>

  <script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('deviceChart').getContext('2d');

    var deviceData = <?php echo $device_stats_json; ?>;

    // Generate a unique color for each device type
    function generateRandomColor() {
        return '#' + Math.floor(Math.random()*16777215).toString(16);
    }

    var labels = Object.keys(deviceData);
    var data = Object.values(deviceData);
    var backgroundColors = labels.map(() => generateRandomColor());

    var chartData = {
        labels: labels,
        datasets: [{
            label: 'Device Usage',
            data: data,
            backgroundColor: backgroundColors,
            hoverOffset: 4
        }]
    };

    var deviceChart = new Chart(ctx, {
        type: 'pie',
        data: chartData,
        options: {
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            var dataset = tooltipItem.dataset;
                            var value = dataset.data[tooltipItem.dataIndex];
                            var label = dataset.labels[tooltipItem.dataIndex] || '';
                            return label + ': ' + value;
                        }
                    }
                }
            }
        }
    });
});
</script>



  <script>
    // Function to handle the collapsible functionality
    function handleCollapsible() {
      var coll = document.getElementsByClassName("collapsible");
      for (var i = 0; i < coll.length; i++) {
        coll[i].addEventListener("click", function() {
          this.classList.toggle("active");
          var content = this.nextElementSibling;
          if (content.style.maxHeight) {
            content.style.maxHeight = null;
            content.classList.remove('show');
            localStorage.setItem(this.dataset.id, 'closed');
          } else {
            content.style.maxHeight = content.scrollHeight + "px";
            content.classList.add('show');
            localStorage.setItem(this.dataset.id, 'open');
          }
        });
      }
    }

    // Function to load the state from localStorage
    function loadPreferences() {
      var coll = document.getElementsByClassName("collapsible");
      for (var i = 0; i < coll.length; i++) {
        var id = coll[i].dataset.id;
        var state = localStorage.getItem(id);
        if (state === 'open') {
          coll[i].classList.add("active");
          var content = coll[i].nextElementSibling;
          content.style.maxHeight = content.scrollHeight + "px";
          content.classList.add('show');
        } else {
          coll[i].classList.remove("active");
          var content = coll[i].nextElementSibling;
          content.style.maxHeight = null;
          content.classList.remove('show');
        }
      }
    }

    // Default open the 'Statistics' section
    document.addEventListener("DOMContentLoaded", function() {
      var statisticsButton = document.querySelector('.collapsible[data-id="statistics"]');
      if (statisticsButton) {
        statisticsButton.classList.add("active");
        var content = statisticsButton.nextElementSibling;
        content.style.maxHeight = content.scrollHeight + "px";
        content.classList.add('show');
        localStorage.setItem("statistics", "open");
      }

      handleCollapsible();
      loadPreferences();
    });
  </script>

</body>
</html>
