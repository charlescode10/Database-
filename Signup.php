I have reviewed your code, fixed the potential issues, and translated the Spanish phrases into English. Here’s the updated and improved code:

### Issues Fixed:
1. **Improper validation for sanitized string**: The `filter_var()` function is incorrectly used. It should validate an email or URL. For simple string sanitization, `mysqli_real_escape_string` is already enough.
2. **Security Improvements**: The password is hashed using `md5()` which is insecure. I've replaced it with `password_hash()` for better security.

### Translated Messages:
- "En línea" → "Online"
- "Este usuario no existe." → "This user does not exist."
- "Algo salió mal, intenta de nuevo." → "Something went wrong, please try again."
- "Por favor, selecciona una imagen con formato .jpeg, .png, .jpg" → "Please select an image with a .jpeg, .png, or .jpg format."
- "$user no es un usuario valido." → "$user is not a valid username."
- "All fields are required." is already in English.

Here’s the updated code:

```php
<?php
session_start();
include_once "config.php";

$fname = mysqli_real_escape_string($conn, $_POST['fname']);
$lname = mysqli_real_escape_string($conn, $_POST['lname']);
$user = mysqli_real_escape_string($conn, $_POST['user']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

if(!empty($fname) && !empty($lname) && !empty($user) && !empty($password)){
    // String sanitization is already done with mysqli_real_escape_string
    $sql = mysqli_query($conn, "SELECT * FROM users WHERE user = '{$user}'");
    if(mysqli_num_rows($sql) > 0){
        echo "$user - User already exists.";
    } else {
        if(isset($_FILES['image'])){
            $img_name = $_FILES['image']['name'];
            $img_type = $_FILES['image']['type'];
            $tmp_name = $_FILES['image']['tmp_name'];

            $img_explode = explode('.', $img_name);
            $img_ext = end($img_explode);

            $extensions = ["jpeg", "png", "jpg"];
            if(in_array($img_ext, $extensions) === true){
                $types = ["image/jpeg", "image/jpg", "image/png"];
                if(in_array($img_type, $types) === true){
                    $time = time();
                    $new_img_name = $time.$img_name;
                    if(move_uploaded_file($tmp_name, "images/".$new_img_name)){
                        $ran_id = rand(time(), 100000000);
                        $status = "Online";
                        // Use password_hash instead of md5 for better security
                        $encrypt_pass = password_hash($password, PASSWORD_DEFAULT);
                        $insert_query = mysqli_query($conn, "INSERT INTO users (unique_id, fname, lname, user, password, img, status)
                            VALUES ({$ran_id}, '{$fname}', '{$lname}', '{$user}', '{$encrypt_pass}', '{$new_img_name}', '{$status}')");
                        if($insert_query){
                            $select_sql2 = mysqli_query($conn, "SELECT * FROM users WHERE user = '{$user}'");
                            if(mysqli_num_rows($select_sql2) > 0){
                                $result = mysqli_fetch_assoc($select_sql2);
                                $_SESSION['unique_id'] = $result['unique_id'];
                                echo "success";
                            } else {
                                echo "This user does not exist.";
                            }
                        } else {
                            echo "Something went wrong, please try again.";
                        }
                    }
                } else {
                    echo "Please select an image with a .jpeg, .png, or .jpg format.";
                }
            } else {
                echo "Please select an image with a .jpeg, .png, or .jpg format.";
            }
        }
    }
} else {
    echo "All fields are required.";
}
?>
```

### Key Changes:
- Improved password hashing by using `password_hash()` instead of `md5()` for better security.
- Removed redundant `filter_var()` check.
- Translated messages to English.
- Ensured proper validation and error handling.

This should work better and provide clearer feedback in English.
