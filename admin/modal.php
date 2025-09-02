<?php
// STUDENT MODALS
$students = mysqli_query($con, "SELECT * FROM students ORDER BY student_id DESC") or die(mysqli_error($con));
while ($row = mysqli_fetch_assoc($students)) {
    $id = $row['student_id'];
?>

<style>
  .text-uppercase {
  text-transform: uppercase;
}

</style>
<!-- View Student Modal -->
<div class="modal fade" id="viewStudent<?php echo $id; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">View Student</h4>
      </div>
      <div class="modal-body">
        <p><strong>Name:</strong> <?php echo $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']; ?></p>
        <p><strong>Contact:</strong> <?php echo $row['contact_number']; ?></p>
        <p><strong>Grade & Section:</strong> <?php echo $row['grade_level'] . ' - ' . $row['section']; ?></p>
        <p><strong>Address:</strong> <?php echo $row['address']; ?></p>
        <p><strong>Username:</strong> <?php echo $row['username']; ?></p>
        <p><strong>Password:</strong> <?php echo str_repeat('*', strlen($row['password'])); ?></p>
        <p><strong>Date Registered:</strong> <?php echo $row['date_registered']; ?></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudent<?php echo $id; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <form method="POST" action="update_student.php">
        <div class="modal-header">
          <h4 class="modal-title">Edit Student</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="student_id" value="<?php echo $id; ?>">

          <input type="text" name="firstname" class="form-control text-uppercase" 
                 value="<?php echo $row['firstname']; ?>" placeholder="First Name" required><br>

          <input type="text" name="middlename" class="form-control text-uppercase" 
                 value="<?php echo $row['middlename']; ?>" placeholder="Middle Name"><br>

          <input type="text" name="lastname" class="form-control text-uppercase" 
                 value="<?php echo $row['lastname']; ?>" placeholder="Last Name" required><br>

          <input type="text" name="grade_level" class="form-control" 
                 value="<?php echo $row['grade_level']; ?>" placeholder="Grade Level"><br>

          <input type="text" name="section" class="form-control" 
                 value="<?php echo $row['section']; ?>" placeholder="Section"><br>

          <input type="text" name="address" class="form-control text-uppercase" 
                 value="<?php echo $row['address']; ?>" placeholder="Address"><br>

          <input type="text" name="username" class="form-control" 
                 value="<?php echo $row['username']; ?>" placeholder="Username"><br>

          <input type="password" name="password" class="form-control" 
                 value="<?php echo $row['password']; ?>" placeholder="Password"><br>

          <!-- Contact Validation -->
          <input type="text" name="contact_number" class="form-control" 
                 value="<?php echo $row['contact_number']; ?>" placeholder="Contact Number" 
                 pattern="^09[0-9]{9}$" maxlength="11" minlength="11" 
                 title="Contact number must start with 09 and be exactly 11 digits" required><br>

        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button class="btn btn-success" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php } ?>


<?php
// TEACHER MODALS
$teachers = mysqli_query($con, "SELECT * FROM teachers ORDER BY teacher_id DESC") or die(mysqli_error($con));
while ($row = mysqli_fetch_assoc($teachers)) {
    $id = $row['teacher_id'];
?>
<!-- View Teacher Modal -->
<div class="modal fade" id="viewTeacher<?php echo $id; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">View Teacher</h4>
      </div>
      <div class="modal-body">
        <p><strong>Name:</strong> <?php echo $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']; ?></p>
        <p><strong>Contact:</strong> <?php echo $row['contact_number']; ?></p>
        <p><strong>Address:</strong> <?php echo $row['address']; ?></p>
        <p><strong>Username:</strong> <?php echo $row['username']; ?></p>
        <p><strong>Password:</strong> <?php echo str_repeat('*', strlen($row['password'])); ?></p>
        <p><strong>Date Registered:</strong> <?php echo $row['date_registered']; ?></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Teacher Modal -->
<div class="modal fade" id="editTeacher<?php echo $id; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <form method="POST" action="update_teacher.php">
        <div class="modal-header">
          <h4 class="modal-title">Edit Teacher</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="teacher_id" value="<?php echo $id; ?>">

          <input type="text" name="firstname" class="form-control text-uppercase" 
                 value="<?php echo $row['firstname']; ?>" placeholder="First Name" required><br>

          <input type="text" name="middlename" class="form-control text-uppercase" 
                 value="<?php echo $row['middlename']; ?>" placeholder="Middle Name"><br>

          <input type="text" name="lastname" class="form-control text-uppercase" 
                 value="<?php echo $row['lastname']; ?>" placeholder="Last Name" required><br>

          <input type="text" name="address" class="form-control text-uppercase" 
                 value="<?php echo $row['address']; ?>" placeholder="Address"><br>

          <input type="text" name="username" class="form-control" 
                 value="<?php echo $row['username']; ?>" placeholder="Username"><br>

          <input type="password" name="password" class="form-control" 
                 value="<?php echo $row['password']; ?>" placeholder="Password"><br>

          <!-- ✅ Contact number validation -->
          <input type="text" name="contact_number" class="form-control" 
                 value="<?php echo $row['contact_number']; ?>" placeholder="Contact Number"
                 pattern="^09[0-9]{9}$" minlength="11" maxlength="11"
                 title="Contact number must start with 09 and be exactly 11 digits" required><br>

        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button class="btn btn-success" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php } ?>
