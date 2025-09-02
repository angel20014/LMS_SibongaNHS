<?php include('header.php'); ?> 
<?php include('modal.php'); ?> 

<?php
$student_count_result = mysqli_query($con, "SELECT COUNT(*) as total FROM students");
$student_total = mysqli_fetch_assoc($student_count_result)['total'];

$teacher_count_result = mysqli_query($con, "SELECT COUNT(*) as total FROM teachers");
$teacher_total = mysqli_fetch_assoc($teacher_count_result)['total'];
?>

<style>
    body {
        overflow-y: auto;
    }
    .btn-toggle {
        margin: 0 5px;
        font-size: 15px;
    }

    .toggle-section {
        display: none;
    }

    .toggle-section.active {
        display: block;
    }

    .action-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0;
    flex-wrap: nowrap; /* 👈 prevents wrapping */
    }
    
</style>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> User Lists</h3>
         <div><input type="text" class="form-control" id="searchInput" placeholder="Search..." style="width: 280px; margin-left: 820px;"></div>
        
         <div class="action-bar">
             <button class="btn btn-info btn-toggle" onclick="toggleSection('allUsers')">All Users List</button>
    <button class="btn btn-primary btn-toggle" onclick="toggleSection('students')">Students List</button>
    <button class="btn btn-success btn-toggle" onclick="toggleSection('teachers')">Teachers List</button>

    <button class="btn btn-danger" data-toggle="modal" data-target="#printUsersModal">
    <i class="fa fa-print"></i> Print UsersList
</button>
</div>

    
</div>


<div class="clearfix"></div>

<!-- ALL USERS SECTION -->
<div id="allUsers" class="toggle-section">
    <div class="x_panel">
        <div class="x_title">
            <h2><i class="fa fa-users"></i> All Users</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Date Registered</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Combine students and teachers in one list
                $all_users_query = "
                    SELECT 
                        studentid_number AS user_id, 
                        firstname, middlename, lastname, 
                        grade_level, section, 
                        contact_number, address, username, password, 
                        date_registered, 
                        'Student' AS role
                    FROM students
                    UNION ALL
                    SELECT 
                        teacher_id AS user_id, 
                        firstname, middlename, lastname, 
                        '' AS grade_level, '' AS section, 
                        contact_number, address, username, password, 
                        date_registered, 
                        'Teacher' AS role
                    FROM teachers
                    ORDER BY date_registered DESC
                ";
                $all_users = mysqli_query($con, $all_users_query) or die(mysqli_error($con));
                while ($row = mysqli_fetch_assoc($all_users)) {
                ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']; ?></td>
                        <td><?php echo $row['role']; ?></td>
                        <td><?php echo !empty($row['grade_level']) ? $row['grade_level'] : 'N/A'; ?></td>
                        <td><?php echo !empty($row['section']) ? $row['section'] : 'N/A'; ?></td>
                        <td><?php echo $row['contact_number']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo str_repeat('*', strlen($row['password'])); ?></td>
                        <td><?php echo $row['date_registered']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- STUDENTS SECTION -->
<div id="students" class="toggle-section active">
    <div class="x_panel">
        <div class="x_title">
             <h2> <i class="fa fa-users"></i> Students   
             <span style="display: inline-block; background-color: #007bff; color: white; font-size: 18px; font-weight: bold; width: 35px; height: 35px; line-height: 35px; text-align: center; border-radius: 50%; margin-left: 10px;">
                <?php echo $student_total; ?>
             </span></h2>
            <div class="clearfix"></div>
        </div>

        <div class="x_content table-responsive">
    
            <table class="table table-striped table-bordered" id="studentsTable">
                <thead>
        <tr>
        <th>Student ID No.</th>
        <th>Full Name</th>
        <th>Grade Level</th>
        <th>Section</th>
        <th>Contact</th>
        <th>Address</th>
        <th>Username</th> <!-- ✅ Added -->
        <th>Password</th> <!-- ✅ Added -->
        <th>Date Registered</th>
        <th>Action</th>
    </tr>
</thead>
<tbody>
    <?php
    $students = mysqli_query($con, "SELECT * FROM students ORDER BY student_id DESC") or die(mysqli_error($con));
    while ($row = mysqli_fetch_assoc($students)) {
        $id = $row['student_id'];
    ?>
    <tr>
        <td><a target="_blank" href="print_barcode_individual.php?code=<?php echo $row['studentid_number']; ?>"><?php echo $row['studentid_number']; ?></a></td>
        <td><?php echo $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']; ?></td>
        <td><?php echo $row['grade_level']; ?></td>
        <td><?php echo $row['section']; ?></td>
        <td><?php echo $row['contact_number']; ?></td>
        <td><?php echo $row['address']; ?></td>
        <td><?php echo $row['username']; ?></td> <!-- ✅ Added -->
        <td><?php echo str_repeat('*', strlen($row['password'])); ?></td> <!-- ✅ Asterisks -->
        <td><?php echo $row['date_registered']; ?></td>
        <td>
           <button class="btn btn-primary" data-toggle="modal" data-target="#viewStudent<?php echo $id; ?>"><i class="fa fa-search"></i></button>
           <button class="btn btn-warning" data-toggle="modal" data-target="#editStudent<?php echo $id; ?>"><i class="fa fa-edit"></i></button>
        </td>
    </tr>
    <?php } ?>
</tbody>

            </table>
        </div>
    </div>
</div>

<!-- TEACHERS SECTION -->
<div id="teachers" class="toggle-section">
    <div class="x_panel">
        <div class="x_title">
            <h2><i class="fa fa-users"></i> Teachers  
            <span style="display: inline-block; background-color: #007bff; color: white; font-size: 18px; font-weight: bold; width: 35px; height: 35px; line-height: 35px; text-align: center; border-radius: 50%; margin-left: 10px;">
                <?php echo $teacher_total; ?>
            </span></h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content table-responsive">
            <table class="table table-striped table-bordered" id="teachersTable">
                <thead>
    <tr>
        <th>Teacher ID</th>
        <th>Full Name</th>
        <th>Contact</th>
        <th>Address</th>
        <th>Username</th> <!-- ✅ Added -->
        <th>Password</th> <!-- ✅ Added -->
        <th>Date Registered</th>
        <th>Action</th>
    </tr>
</thead>
<tbody>
    <?php
    $teachers = mysqli_query($con, "SELECT * FROM teachers ORDER BY teacher_id DESC") or die(mysqli_error($con));
    while ($row = mysqli_fetch_assoc($teachers)) {
        $id = $row['teacher_id'];
    ?>
    <tr>
        <td><a target="_blank" href="print_barcode_individual.php?code=<?php echo $row['teacher_id']; ?>"><?php echo $row['teacher_id']; ?></a></td>
        <td><?php echo $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']; ?></td>
        <td><?php echo $row['contact_number']; ?></td>
        <td><?php echo $row['address']; ?></td>
        <td><?php echo $row['username']; ?></td> <!-- ✅ Added -->
        <td><?php echo str_repeat('*', strlen($row['password'])); ?></td> <!-- ✅ Asterisks -->
        <td><?php echo $row['date_registered']; ?></td>
        <td>
           <button class="btn btn-primary" data-toggle="modal" data-target="#viewTeacher<?php echo $id; ?>"><i class="fa fa-search"></i></button>
           <button class="btn btn-warning" data-toggle="modal" data-target="#editTeacher<?php echo $id; ?>"><i class="fa fa-edit"></i></button>
        </td>
    </tr>
    <?php } ?>
</tbody>

            </table>
        </div>
    </div>
</div>
</div>


<!-- Print Users Modal -->
<div class="modal fade" id="printUsersModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <form method="GET" action="member_print.php" target="_blank">
        <div class="modal-header">
          <h4 class="modal-title">Print Users</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">

          <!-- Date From -->
          <label for="date_from">Date From:</label>
          <input type="date" name="date_from" id="date_from" class="form-control" required><br>

          <!-- Date To -->
          <label for="date_to">Date To:</label>
          <input type="date" name="date_to" id="date_to" class="form-control" required><br>

          <!-- Print Options -->
          <label><strong>Print Option:</strong></label><br>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="print_option" id="print_all" value="all" checked>
            <label class="form-check-label" for="print_all">All</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="print_option" id="print_students" value="students">
            <label class="form-check-label" for="print_students">Students Only</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="print_option" id="print_teachers" value="teachers">
            <label class="form-check-label" for="print_teachers">Teachers Only</label>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="fa fa-print"></i> Print
          </button>
        </div>
      </form>
    </div>
  </div>
</div>



<script>
function toggleSection(section) {
    document.querySelectorAll('.toggle-section').forEach(el => el.classList.remove('active'));
    document.getElementById(section).classList.add('active');
}
document.getElementById('searchInput').addEventListener('keyup', function () {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.toggle-section.active table tbody tr').forEach(row => {
        row.style.display = [...row.children].some(td => td.textContent.toLowerCase().includes(filter)) ? '' : 'none';
    });
});
</script>

<?php include('footer.php'); ?> 