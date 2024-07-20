# Database-


To achieve this, you'll need to set up a backend server with a database to handle the storage and retrieval of user data. Here's a step-by-step guide using Node.js with Express and MongoDB.

### Step 1: Set Up Your Project

1. **Initialize your project:**
    ```sh
    npm init -y
    ```

2. **Install necessary packages:**
    ```sh
    npm install express mongoose bcryptjs jsonwebtoken body-parser cookie-parser
    ```

### Step 2: Set Up Your Database

1. **Install MongoDB**:
   Follow the instructions to install MongoDB on your system from [MongoDB's official documentation](https://docs.mongodb.com/manual/installation/).

2. **Create a database:**
   You can use MongoDB Compass or the MongoDB shell to create a database called `loyal` and a collection called `users`.

### Step 3: Create Your Backend Server

1. **Create a file named `server.js`:**
    ```javascript
    const express = require('express');
    const mongoose = require('mongoose');
    const bcrypt = require('bcryptjs');
    const jwt = require('jsonwebtoken');
    const bodyParser = require('body-parser');
    const cookieParser = require('cookie-parser');
    const app = express();

    // Middleware
    app.use(bodyParser.urlencoded({ extended: true }));
    app.use(bodyParser.json());
    app.use(cookieParser());

    // MongoDB connection
    mongoose.connect('mongodb://localhost:27017/loyal', {
        useNewUrlParser: true,
        useUnifiedTopology: true
    });

    // User schema
    const userSchema = new mongoose.Schema({
        username: String,
        email: String,
        password: String
    });

    const User = mongoose.model('User', userSchema);

    // Register route
    app.post('/register', async (req, res) => {
        const { username, email, password } = req.body;
        const hashedPassword = await bcrypt.hash(password, 10);
        const newUser = new User({ username, email, password: hashedPassword });

        try {
            await newUser.save();
            res.status(201).send('User registered');
        } catch (error) {
            res.status(400).send('Error registering user');
        }
    });

    // Login route
    app.post('/login', async (req, res) => {
        const { email, password } = req.body;
        const user = await User.findOne({ email });

        if (!user || !(await bcrypt.compare(password, user.password))) {
            return res.status(401).send('Invalid credentials');
        }

        const token = jwt.sign({ userId: user._id }, 'your_jwt_secret', { expiresIn: '1h' });
        res.cookie('loyal_users', token, { httpOnly: true }).send('Logged in');
    });

    // Middleware to verify token
    const verifyToken = (req, res, next) => {
        const token = req.cookies.loyal_users;
        if (!token) {
            return res.status(401).send('Access denied');
        }
        try {
            const verified = jwt.verify(token, 'your_jwt_secret');
            req.user = verified;
            next();
        } catch (error) {
            res.status(400).send('Invalid token');
        }
    };

    // Protected route
    app.get('/home', verifyToken, (req, res) => {
        res.send('Welcome to the home page');
    });

    // Logout route
    app.post('/logout', (req, res) => {
        res.clearCookie('loyal_users').send('Logged out');
    });

    // Start server
    app.listen(3000, () => {
        console.log('Server is running on port 3000');
    });
    ```

### Step 4: Update Your HTML Forms to Send Data to Your Server

1. **Update the form action URLs in your HTML to point to your backend routes:**

    ```html
    <form action="/register" method="POST">
        ...
    </form>

    <form action="/login" method="POST">
        ...
    </form>
    ```

2. **Update the form tags in your HTML to include the appropriate attributes:**

    ```html
    <form action="/register" method="POST">
        <h1>Create Account</h1>
        ...
    </form>

    <form action="/login" method="POST">
        <h1>Sign in</h1>
        ...
    </form>
    ```

### Step 5: Set Up Your Frontend to Handle Sessions

1. **Modify your JavaScript to handle session management and redirection:**

    ```javascript
    // After successful login, redirect to the home page
    document.querySelector('form[action="/login"]').addEventListener('submit', function (e) {
        e.preventDefault();

        fetch('/login', {
            method: 'POST',
            body: new URLSearchParams(new FormData(e.target))
        }).then(res => {
            if (res.ok) {
                window.location.href = '/home';
            } else {
                alert('Login failed');
            }
        });
    });

    // Handle logout
    document.getElementById('logout').addEventListener('click', function () {
        fetch('/logout', {
            method: 'POST'
        }).then(() => {
            window.location.href = '/';
        });
    });
    ```

### Step 6: Create Your Protected Home Page

1. **Create an `home.html` file:**

    ```html
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
    </head>
    <body>
        <h1>Welcome to the Home Page</h1>
        <button id="logout">Logout</button>
        <script src="./script.js"></script>
    </body>
    </html>
    ```

### Step 7: Start Your Server and Test

1. **Start your server:**
    ```sh
    node server.js
    ```

2. **Open your browser and test the registration, login, and session management functionalities.**

By following these steps, you'll have a functional registration and login system with session management using Node.js, Express, and MongoDB.





















I understand you want to use PHP and MySQL for your project. Let's proceed with setting up a PHP-based backend to handle user registration, login, and session management.

### Step 1: Set Up Your Database

1. **Create the `loyal` database and `users` table:**

    ```sql
    CREATE DATABASE loyal;

    USE loyal;

    CREATE TABLE users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    );
    ```

### Step 2: Create Your PHP Files

1. **Create a file named `config.php` for database configuration:**

    ```php
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "your_password";
    $dbname = "loyal";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    ?>
    ```

2. **Create a file named `register.php` for user registration:**

    ```php
    <?php
    session_start();
    include 'config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        $conn->close();
    }
    ?>
    ```

3. **Create a file named `login.php` for user login:**

    ```php
    <?php
    session_start();
    include 'config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['user_id'];
                header("Location: home.php");
            } else {
                echo "Invalid password";
            }
        } else {
            echo "No user found with this email";
        }

        $conn->close();
    }
    ?>
    ```

4. **Create a file named `home.php` for the home page after login:**

    ```php
    <?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.html");
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Home</title>
    </head>
    <body>
        <h1>Welcome to the Home Page</h1>
        <a href="logout.php">Logout</a>
    </body>
    </html>
    ```

5. **Create a file named `logout.php` for logging out:**

    ```php
    <?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: index.html");
    ?>
    ```

### Step 3: Update Your HTML Forms to Point to Your PHP Scripts

1. **Update the form action URLs in your HTML to point to your PHP scripts:**

    ```html
    <form action="register.php" method="POST">
        <h1>Create Account</h1>
        <div class="infield">
            <i class="fas fa-user" id="icons"></i>
            <input type="text" name="username" placeholder="User Name" required>
            <label></label>
        </div>
        <div class="infield">
            <i class="fas fa-envelope" id="icons"></i>
            <input type="email" name="email" placeholder="Email" required>
            <label></label>
        </div>
        <div class="infield">
            <i class="fas fa-key" id="icons"></i>
            <input type="password" name="password" placeholder="Password" required>
            <label></label>
        </div>
        <button type="submit">Submit</button>
        <p class="animation_forms">Already have an account <a href="#" id="goToLogin">LOGIN</a></p>
    </form>

    <form action="login.php" method="POST">
        <h1>Sign in</h1>
        <div class="infield">
            <i class="fas fa-envelope" id="icons"></i>
            <input type="email" name="email" placeholder="Email" required>
            <label></label>
        </div>
        <div class="infield">
            <i class="fas fa-key" id="icons"></i>
            <input type="password" name="password" placeholder="Password" minlength="8" maxlength="16" required>
            <label></label>
        </div>
        <a href="../error/error.html" class="forgot">Forgot your password?</a>
        <button type="submit">Log In</button>
        <p class="animation_forms">Don't have an account ?<a href="#" id="goToSignup">SIGNUP</a></p>
    </form>
    ```

### Step 4: Test Your Setup

1. **Start your local server using XAMPP, WAMP, or any other server software.**

2. **Place your HTML and PHP files in the appropriate directories (e.g., `htdocs` for XAMPP).**

3. **Open your browser and navigate to your HTML file to test the registration, login, and session management functionalities.**

By following these steps, you'll have a functional registration and login system with session management using PHP and MySQL.
