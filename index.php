<?php
include('include/dbcon.php');
$showLogin = isset($_GET['showLogin']) && $_GET['showLogin'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sibonga NHS - Library Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-cover bg-center min-h-screen relative" style="background-image: url('images/bg1.jpg');">
<div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>

<nav class="fixed top-0 left-0 right-0 bg-white text-gray-800 shadow z-10">
  <div class="container mx-auto flex items-center justify-between px-4 py-2">
    <a href="#" class="flex items-center">
      <img src="images/LOGOLIBRARY.png" alt="Library Logo" class="h-12">
    </a>
    <ul class="flex space-x-6 text-lg font-bold">
      <li><a href="#" class="hover:text-gray-600"><i class="bi bi-house-door-fill"></i> Home</a></li>
      <li><a href="#" onclick="toggleModal('loginModal')" class="hover:text-gray-600"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
      <li><a href="#" onclick="toggleModal('registerType')" class="hover:text-gray-600"><i class="bi bi-person-plus-fill"></i> Register</a></li>
    </ul>
  </div>
</nav>

<div class="relative z-0 text-white text-center pt-40 px-4">
  <img src="images/logo.png" alt="School Logo"
       class="mx-auto w-[140px] h-[140px] mb-6 animate-pulse"
       style="filter: drop-shadow(0 0 10px #a0e9ff) drop-shadow(0 0 20px white);">
  <h1 class="text-3xl md:text-5xl font-bold italic drop-shadow-md">Welcome to Library Management System</h1>
  <p class="text-xl mt-2 italic">Towards a User Frequency and  Automated Platforms</p>
</div>

<!-- Modal Template -->
<div id="modalWrapper" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-50 z-50">
  <div id="modalContent" class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xl relative max-h-[90vh] overflow-y-auto">
    <button onclick="closeModal()" class="absolute top-2 right-2 text-xl font-bold">&times;</button>
    <div id="modalBody"></div>
  </div>
</div>

<script>
function toggleModal(type) {
  let content = '';

  if (type === 'loginModal') {
    content = `
      <form action="login.php" method="post" class="space-y-4">
        <div class="col-span-2 flex justify-center"> <img src="images/logo.png" alt="Logo" class="w-20 h-20 mb-2"> </div>
        <input type="text" name="username" class="w-full px-4 py-2 border rounded" placeholder="Username" required>
        <input type="password" name="password" class="w-full px-4 py-2 border rounded" placeholder="Password" required>

         <div class="flex justify-between items-center text-sm text-gray-700 mb-2">
            <label class="flex items-center"> <input type="checkbox" name="remember" class="mr-2"> Remember Me </label>
            <a href="#" onclick="toggleModal('forgotPassword')" class="text-blue-600 hover:underline">Forgot Password?</a>
          </div>
        <button type="submit" name="login" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
          <i class="bi bi-box-arrow-in-right"></i> Login
        </button>

        <!-- OR Divider -->
<div class="flex items-center my-4">
  <hr class="flex-grow border-t border-gray-300">
  <span class="px-2 text-gray-500 text-sm">OR</span>
  <hr class="flex-grow border-t border-gray-300">
</div>

        <p class="text-center text-sm mt-2">  
          Don’t have an account?
          <button type="button" onclick="toggleModal('registerType')" class="text-blue-600 underline">Register</button>
        </p>
      </form>`;
 } else if (type === 'registerType') {
  content = `
    <form action="register.php" method="post" enctype="multipart/form-data" class="grid grid-cols-2 gap-4" id="registerForm">
      <div class="col-span-2 flex justify-center">
        <img src="images/logo.png" alt="Logo" class="w-20 h-20 mb-2">
      </div>
     
      <div class="col-span-2">
        <select name="usertype" id="usertypeRegister" class="w-full px-4 py-2 border rounded" required>
          <option value="">Select User Type</option>
          <option value="student">Student</option>
          <option value="admin">Admin</option>
          <option value="teacher">Teacher</option>
        </select>
      </div>

      <div id="studentFields" class="col-span-2 grid grid-cols-2 gap-4 hidden">
        <input type="number" name="studentid_number" class="w-full px-4 py-2 border rounded" placeholder="Student ID number" onblur="checkDuplicate('studentid_number', this)">
        <span id="studentIdError" class="text-red-600 text-sm col-span-2 hidden"></span>
        <select name="grade_level" class="w-full px-4 py-2 border rounded">
          <option value="">Select Grade Level</option>
          <option value="Grade 7">Grade 7</option>
          <option value="Grade 8">Grade 8</option>
          <option value="Grade 9">Grade 9</option>
          <option value="Grade 10">Grade 10</option>
          <option value="Grade 11">Grade 11</option>
          <option value="Grade 12">Grade 12</option>
        </select>
        <input type="text" name="section" id="section" class="w-full px-4 py-2 border rounded col-span-2" placeholder="Section">
      </div>

      <input type="text" name="firstname" id="firstname" class="w-full px-4 py-2 border rounded" placeholder="First Name" required>
      <input type="text" name="username" class="w-full px-4 py-2 border rounded" placeholder="Username" required onblur="checkDuplicate('username', this)">
      <span id="usernameError" class="text-red-600 text-sm col-span-2 hidden"></span>
      <input type="text" name="middlename" id="middlename" class="w-full px-4 py-2 border rounded" placeholder="Middle Name">
      <input type="password" name="password" class="w-full px-4 py-2 border rounded" placeholder="Password" required>
      <input type="text" name="lastname" id="lastname" class="w-full px-4 py-2 border rounded" placeholder="Last Name" required>

      <!-- Address field -->
      <input type="text" name="address" id="address" class="w-full px-4 py-2 border rounded col-span-2" placeholder="Address" required>

      <div class="col-span-2">
  <input type="text" id="contact_number" name="contact_number"
         class="w-full px-4 py-2 border rounded"
         placeholder="09XXXXXXXXX"
         maxlength="11"
         required
         oninput="validateContactNumber(this)"
         pattern="09[0-9]{09}"
         inputmode="numeric">
</div>


      <div class="col-span-2">
        <select name="security_question" class="w-full px-4 py-2 border rounded" required>
          <option value="">Select a Security Question</option>
          <option value="What is your favorite color?">What is your favorite food?</option>
          <option value="What is your pet's name?">What is your favorite shoes?</option>
          <option value="What is your mother's maiden name?">What is your favorite movie?</option>
          <option value="What is your pet's name?">What is your favorite color?</option>
          <option value="What is your mother's maiden name?">What is your pet's name?</option>
        </select>
      </div>

      <input type="text" name="security_answer" class="w-full px-4 py-2 border rounded" placeholder="Security Answer" required>
      <input type="file" name="image" class="w-full px-4 py-2 border rounded">

      <div class="col-span-2">
        <button type="submit" name="register" class="w-full hover:bg-blue-700 bg-blue-600 text-white px-4 py-2 rounded">
          <i class="bi bi-person-plus-fill"></i> Register
        </button>
        <p class="text-center text-sm mt-2">
          Already have an account?
          <button type="button" onclick="toggleModal('loginModal')" class="text-green-600 underline">Login</button>
        </p>
      </div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="col-span-2 bg-red-100 text-red-700 p-2 rounded">
          <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']); 
          ?>
        </div>
      <?php endif; ?>
    </form>`;


  } else if (type === 'forgotPassword') {
  content = `
    <form onsubmit="event.preventDefault(); checkUser();" class="space-y-4" id="forgotForm">
    <div class="flex items-center space-x-2 cursor-pointer text-blue-600 hover:text-blue-800 font-medium" onclick="closeModal()">
    <span class="text-xl">←</span> <span></span></div>
    
      <div class="col-span-2 flex justify-center">
        <img src="images/logo.png" alt="Logo" class="w-20 h-20 mb-2">
      </div>
      <h2 class="text-xl font-bold text-center">Forgot Password</h2>
      <input type="text" id="forgotUsername" placeholder="Enter username or Student ID Number" class="w-full border rounded px-3 py-2" required>
      <button type="button" onclick="checkUser()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"> Proceed</button>
      
      <div id="securitySection" class="hidden space-y-4">
        <p class="text-gray-700 font-semibold" id="securityQuestion"></p>
        <input type="text" id="securityAnswer" class="w-full px-4 py-2 border rounded" placeholder="Your Answer" required>
        <button type="button" onclick="verifyAnswer()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Proceed</button>
      </div>
      <div id="resetSection" class="hidden space-y-4">
        <input type="password" id="newPassword" class="w-full px-4 py-2 border rounded" placeholder="New Password" required>
        <input type="password" id="confirmPassword" class="w-full px-4 py-2 border rounded" placeholder="Confirm Password" required>
        <button type="button" onclick="resetPassword()" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
          Reset Password
        </button>
      </div>

    </form>
  `;
}


  const modalBody = document.getElementById('modalBody');
modalBody.innerHTML = content;
document.getElementById('modalWrapper').classList.remove('hidden');
document.getElementById('modalWrapper').classList.add('flex');


setTimeout(() => {
  ['firstname', 'middlename', 'lastname', 'section'].forEach(function (id) {
    const input = document.getElementById(id);
    if (input) {
      input.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
      });
    }
  });
}, 100);


  setTimeout(() => {
  const usertype = document.getElementById('usertypeRegister');
  if (usertype) {
    const studentFields = document.getElementById('studentFields');
    const teacherFields = document.getElementById('teacherFields');
    const studentId = document.querySelector('[name="studentid_number"]');
    const gradeLevel = document.querySelector('[name="grade_level"]');
    const section = document.querySelector('[name="section"]');
    const teacherId = document.querySelector('[name="teacher_id"]');
    const imageInput = document.querySelector('[name="image"]');
    const addressInput = document.querySelector('[name="address"]'); // Address field

    usertype.addEventListener('change', function () {
      const selected = this.value;

      studentFields.classList.toggle('hidden', selected !== 'student');
      teacherFields.classList.toggle('hidden', selected !== 'teacher');

      if (selected === 'student') {
        studentId.setAttribute('required', true);
        gradeLevel.setAttribute('required', true);
        section.setAttribute('required', true);
        teacherId.removeAttribute('required');
        imageInput.removeAttribute('required');
        addressInput.setAttribute('required', true); // Always required
      } 
      else if (selected === 'teacher') {
        teacherId.setAttribute('required', true);
        studentId.removeAttribute('required');
        gradeLevel.removeAttribute('required');
        section.removeAttribute('required');
        imageInput.removeAttribute('required');
        addressInput.setAttribute('required', true); // Always required
      } 
      else if (selected === 'admin') {
        imageInput.setAttribute('required', true);
        studentId.removeAttribute('required');
        gradeLevel.removeAttribute('required');
        section.removeAttribute('required');
        teacherId.removeAttribute('required');
        addressInput.setAttribute('required', true); // Always required
      }
    });
  }
}, 100);

}

function closeModal() {
  document.getElementById('modalWrapper').classList.add('hidden');
  document.getElementById('modalWrapper').classList.remove('flex');
  document.getElementById('modalBody').innerHTML = '';
}
</script>

<script>
function validateContactNumber(input) {
  // Keep only numbers
  input.value = input.value.replace(/\D/g, '');

  // Force start with '09'
  if (!input.value.startsWith('09')) {
    input.value = '09';
  }

  // Limit to 11 digits
  if (input.value.length > 11) {
    input.value = input.value.slice(0, 11);
  }
}
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('showLogin') === '1') {
      toggleModal('loginModal');
    }
  });
</script>

<script>
let globalUsername = '';
let globalTable = '';

function checkUser() {
  const username = document.getElementById('forgotUsername').value;

  $.post('forgot_password.php', { step: 1, username }, function (data) {
    const res = JSON.parse(data);
    if (res.success) {
      globalUsername = username;
      globalTable = res.table;

      document.getElementById('securityQuestion').textContent = res.question;
      document.getElementById('securitySection').classList.remove('hidden');
      document.getElementById('forgotUsername').classList.add('hidden');
      document.querySelector('#forgotForm button[type="button"]').classList.add('hidden');

    } else {
      alert(res.message || 'Username not found.');
    }
  });
}


function verifyAnswer() {
  const answer = document.getElementById('securityAnswer').value;

  $.post('forgot_password.php', {
    step: 2,
    username: globalUsername,
    table: globalTable,
    secret_answer: answer
  }, function (data) {
    const res = JSON.parse(data);
    if (res.success) {

      document.getElementById('resetSection').classList.remove('hidden');

      document.getElementById('securitySection').classList.add('hidden');
    } else {
      alert(res.message || 'Incorrect answer.');
    }
  });
}


function resetPassword() {
  const newPassword = document.getElementById('newPassword').value;
  const confirmPassword = document.getElementById('confirmPassword').value;

  if (newPassword !== confirmPassword) {
    alert('Passwords do not match.');
    return;
  }

  $.post('forgot_password.php', {
    step: 3,
    username: globalUsername,
    table: globalTable,
    new_password: newPassword,
    confirm_password: confirmPassword
  }, function (data) {
    const res = JSON.parse(data);
    if (res.success) {
      alert('Password updated successfully.');
      closeModal();
      toggleModal('loginModal');
    } else {
      alert(res.message || 'Failed to update password.');
    }
  });
}
</script>

<script>
  function checkDuplicate(field, input) {
    const value = input.value.trim();
    const errorSpan = field === 'username' ? document.getElementById('usernameError') : document.getElementById('studentIdError');

    if (!value) {
      errorSpan.classList.add('hidden');
      return;
    }

    fetch('check_duplicate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `field=${field}&value=${encodeURIComponent(value)}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.exists) {
        errorSpan.textContent = `${field === 'username' ? 'Username' : 'Student ID number'} already exists.`;
        errorSpan.classList.remove('hidden');
        input.classList.add('border-red-500');
      } else {
        errorSpan.classList.add('hidden');
        input.classList.remove('border-red-500');
      }
    });
  }

  document.getElementById('registerForm').addEventListener('submit', function (e) {
    if (!document.getElementById('usernameError').classList.contains('hidden') ||
        !document.getElementById('studentIdError').classList.contains('hidden')) {
      e.preventDefault();
      alert('Please fix duplicate errors before submitting.');
    }
  });
</script>

</body>
</html>
