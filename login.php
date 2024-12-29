<?php 
session_start();
include("database.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginType = $_POST['login'];
    $id = $_POST['id'];
    $password = $_POST['password'];

    // Check if the login is for a doctor (either rheumatologist or physiotherapist)
    if ($loginType === "Rhemutologist" || $loginType === "Physiotherapist") {
        // Query to check if the doctor exists based on their ID, password, and specialization (Field)
        $query = "SELECT * FROM Doctor WHERE DoctorID='$id' AND Password='$password' AND Field='$loginType'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $doctor = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $doctor['DoctorID'];
            $_SESSION['user_name'] = $doctor['Name'];
            
            // Redirect to the appropriate page based on the doctor's specialization
            if ($doctor['Field'] === "Rhemutologist") {
                header("Location: rhemutologist.php");
            } else if ($doctor['Field'] === "Physiotherapist") {
                header("Location: physiotherapist.php");
            }
            exit;
        } else {
            $error = "Incorrect ID, Password, or Doctor Type!";
        }
    } else if ($loginType === "patient") {
        // Query for patient login
        $query = "SELECT * FROM Patient WHERE PatientID='$id' AND Password='$password'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['PatientID'];
            $_SESSION['user_name'] = $user['Name'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Incorrect Patient ID or Password!";
        }
    } else {
        $error = "Invalid login type!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System | Login</title>
    <link rel="stylesheet" href="main-netflix.css">
    <style>
        .suggestions {
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            position: absolute;
            background: #fff;
            z-index: 1000;
            width: 13%;
        }
        .suggestion-item {
            padding: 8px;
            cursor: pointer;
        }
        .suggestion-item:hover {
            background: #f0f0f0;
        }
        .signup-link {
            display: none;
        }
    </style>
</head>
<body>
    <nav>
        <a href="#"><img src="logo.svg" alt="logo"></a>
    </nav>
    <div class="form-wrapper">
        <h2>Login</h2>
        <form method="POST" action="">
            <select id="login" name="login" required>
                <option value="" disabled selected>Select your type</option>
                <option value="Rhemutologist">Rhemutologist</option>
                <option value="patient">Patient</option>
                <option value="Physiotherapist">Physiotherapist</option>
            </select>
            <div style="position: relative;">
                <input type="text" id="idInput" name="id" placeholder="Enter your ID" autocomplete="off" required>
                <div id="suggestions" class="suggestions"></div>
            </div>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if (!empty($error)): ?>
            <p style="color: red;">
                <?= $error ?>
            </p>
        <?php endif; ?>
        <p class="signup-link">New? <a href="signup.php">Sign up now</a></p>
    </div>
    <script>
        const idInput = document.getElementById('idInput');
        const suggestionsBox = document.getElementById('suggestions');
        const loginSelect = document.getElementById('login');
        const signupLink = document.querySelector('.signup-link');

        idInput.addEventListener('input', () => {
            const term = idInput.value;
            const loginType = loginSelect.value;

            if (term.length > 0) {
                let url = '';
                if (loginType === 'Rhemutologist' || loginType === 'Physiotherapist') {
                    url = `fetch_doctor_ids.php?term=${term}`;
                } else if (loginType === 'patient') {
                    url = `fetch_patient_ids.php?term=${term}`;
                }

                if (url) {
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            suggestionsBox.innerHTML = '';

                            data.forEach(id => {
                                const suggestionItem = document.createElement('div');
                                suggestionItem.className = 'suggestion-item';
                                suggestionItem.textContent = id;

                                suggestionItem.addEventListener('click', () => {
                                    idInput.value = id;
                                    suggestionsBox.innerHTML = '';
                                });

                                suggestionsBox.appendChild(suggestionItem);
                            });
                        });
                }
            } else {
                suggestionsBox.innerHTML = '';
            }
        });

        document.addEventListener('click', (e) => {
            if (!suggestionsBox.contains(e.target) && e.target !== idInput) {
                suggestionsBox.innerHTML = '';
            }
        });

        loginSelect.addEventListener('change', () => {
            signupLink.style.display = loginSelect.value === 'patient' ? 'block' : 'none';
        });
    </script>
</body>
</html>
