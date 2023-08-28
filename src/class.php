<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
class Reponser
{

    private function dbConnect()
    {
        $hostname = 'localhost';
        $username = 'root';
        $password = 'BATTULAvarshini@36';
        $dbname = 'rest_api_todos';

        try {
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            return "Connection failed: " . $e->getMessage();
        }

    }
    public function ResProcesser(string $method, string $main_req, $user_name, $user_id): void
    {
        if ($method === "POST" && $main_req === "signUp") {
            $connect = $this->dbConnect();
            if ($connect) {
                $name = $_POST['name'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $conform_password = $_POST['newPassword'];
                $gender = $_POST['gender'];

                if ($password === $conform_password) {
                    if (strlen($name) < 2) {
                        $res = [
                            'message' => 'the length of name should be above 2',
                            'status' => 404

                        ];
                        echo json_encode($res);
                    } else {
                        if (strlen($email) < 5) {
                            $res = [
                                'message' => 'The length of the email should be above 5 characters',
                                'status' => 404

                            ];
                            echo json_encode($res);

                        } else {
                            if (strlen($password) < 4) {
                                $res = [
                                    'message' => 'The length of the Password should be above 5 characters',
                                    'status' => 404

                                ];
                                echo json_encode($res);

                            } else {

                                $encode_password = password_hash($password, PASSWORD_DEFAULT);
                                $letrun = $connect->prepare("SELECT * FROM user_login_ where email = '$email'");
                                $letrun->execute();
                                $result = $letrun->fetchAll(PDO::FETCH_ASSOC);
                                if (count($result) > 0) {
                                    $res = [
                                        'message' => 'Email already exit',
                                        'status' => 404

                                    ];
                                    echo json_encode($res);

                                } else {
                                    $insertQuery = $connect->prepare("INSERT INTO user_login_ (name, email, password, gender) VALUES ('$name', '$email', '$encode_password', '$gender')");
                                    $insertQuery->execute();
                                    $insert_result = $insertQuery->fetchAll(PDO::FETCH_ASSOC);
                                    $letrun = $connect->prepare("SELECT * FROM user_login_ where email = '$email'");
                                    $letrun->execute();
                                    $isinsert = $letrun->fetchAll(PDO::FETCH_ASSOC);
                                    if (count($isinsert) > 0) {
                                        $res = [
                                            'message' => 'Registation Success',
                                            'status' => 200

                                        ];
                                        echo json_encode($res);
                                    } else {
                                        $res = [
                                            'message' => 'Error While create user',
                                            'status' => 502

                                        ];
                                        echo json_encode($res);
                                    }

                                }

                            }

                        }

                    }
                } else {
                    $res = [
                        'message' => 'Password did not match!',
                        'status' => 502

                    ];
                    echo json_encode($res);

                }
            } else {
                $res = [
                    'message' => 'db not connect',
                    'status' => 502

                ];
                echo json_encode($res);
            }
        }
        if ($method === "POST" && $main_req === "login") {
            $connect = $this->dbConnect();

            $password = $_POST["password"] ?? null;
            $email = $_POST['email'] ?? null;
            if ($password !== null) {
                // "SELECT * FROM user_login_ WHERE email = '$email'"
                // $let_check_user = $connect->prepare("call userDetails($email)");
                // $let_check_user->execute();
                // $user = $let_check_user->fetchAll(PDO::FETCH_ASSOC);

                $let_check_user = $connect->prepare("CALL userDetails(:email)");
                $let_check_user->bindParam(':email', $email, PDO::PARAM_STR);
                $let_check_user->execute();
                $user = $let_check_user->fetchAll(PDO::FETCH_ASSOC);

                if (count($user) > 0) {
                    $old_password = $user[0]["password"];
                    $name = $user[0]['name'];
                    $email = $user[0]['email'];
                    $gender = $user[0]['gender'];
                    $userPic = $user[0]['user_pic'];
                    $id = $user[0]['id'];
                    if (password_verify($password, $old_password)) {
                        $_SESSION['id'] = $id;
                        $_SESSION['name'] = $name;
                        $_SESSION['email'] = $email;
                        $_SESSION['gender'] = $gender;

                        $res = [
                            'message' => 'User Checking Success',
                            'status' => 200,
                            "session_id" => $_SESSION['id'],
                            "user_name" => $_SESSION['name'],
                            "email" => $_SESSION["email"],
                            "gender" => $_SESSION["gender"],
                            'user_pic' => $user[0]['user_pic']


                        ];
                        echo json_encode($res);

                    } else {
                        $res = [
                            'message' => 'Invalid Password',
                            'status' => 502,


                        ];
                        echo json_encode($res);
                    }

                } else {
                    $res = [
                        'message' => 'No User Found',
                        'status' => 404,


                    ];
                    echo json_encode($res);
                }
            } else {
                $res = [
                    'message' => 'Please enter required email and password',
                    'status' => 502,


                ];
                echo json_encode($res);

            }

        }
        if ($method === "GET" && $main_req === "logOut") {
            session_unset();
            session_destroy();
            $res = [
                'message' => 'Session Destroyed',
                'status' => 200,


            ];
            echo json_encode($res);



        }
        if ($method === "GET" && $main_req === "showtask") {
            // echo $main_req;

            $connect = $this->dbConnect();
            $id = $user_id;
            $name = $user_name;
            $_SESSION['id'] = $id;
            $_SESSION['name'] = $name;
            $take_todos = $connect->prepare("SELECT * FROM todos_db_rest where userid = :id");
            $take_todos->bindParam(':id', $id);
            $take_todos->execute();
            $result = $take_todos->fetchAll(PDO::FETCH_ASSOC);

            $res = [
                'message' => 'Success',
                'status' => 200,
                'data' => $result
            ];
            echo json_encode($res);


        }
        if ($method === "POST" && $main_req === "addtask") {
            $connect = $this->dbConnect();
            $task = $_POST['task'];
            $id = $_POST['id'];
            $check_duplicates_query = $connect->prepare("SELECT * from todos_db_rest WHERE userid = $id and task_name = '$task'");
            $check_duplicates_query->execute();
            $no_of_rows = count($check_duplicates_query->fetchAll(PDO::FETCH_ASSOC));
            if ($no_of_rows == 1) {
                $res = [
                    'message' => 'Task already added',
                    'status' => 200,
                ];
                echo json_encode($res);
            } else {
                $insert_query_task = $connect->prepare("INSERT INTO todos_db_rest (task_name, userid, is_completed) VALUES ('$task', $id, 'No')");
                $insert_query_task->execute();

                $take_todos = $connect->prepare("SELECT * FROM todos_db_rest where task_name = '$task' and userid = $id");
                $take_todos->execute();
                $result = $take_todos->fetchAll(PDO::FETCH_ASSOC);


                $res = [
                    'message' => 'Success',
                    'status' => 200,
                    'data' => $result
                ];
                echo json_encode($res);

            }




        }
        if ($method === "PUT" && $main_req === "task") {
            $connect = $this->dbConnect();
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $id = $queryParams['id'];
            $idOfTodo = $queryParams["eachId"];

            $isCompleted = $connect->prepare("SELECT is_completed FROM todos_db_rest where id = $idOfTodo");
            $isCompleted->execute();
            $result = $isCompleted->fetch(PDO::FETCH_ASSOC);

            if ($result["is_completed"] === "No") {
                $update_Status = $connect->prepare("UPDATE todos_db_rest SET is_completed = 'Yes' WHERE id = $idOfTodo");
                $update_Status->execute();

            } else {
                $update_Status = $connect->prepare("UPDATE todos_db_rest SET is_completed = 'No' WHERE id = $idOfTodo");
                $update_Status->execute();
            }


            $take_todos = $connect->prepare("SELECT * FROM todos_db_rest where userid = $id and id = $idOfTodo");
            $take_todos->execute();
            $result = $take_todos->fetchAll(PDO::FETCH_ASSOC);
            $res = [
                'message' => 'Success',
                'status' => 200,
                'data' => $result
            ];
            echo json_encode($res);
        }
        if ($method === "DELETE" && $main_req === "task") {
            $connect = $this->dbConnect();
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $id = $_GET['userId'] ?? $queryParams['userId'];
            $idOfTodo = $_GET["taskId"] ?? $queryParams['taskId'];
            $DeleteQuery = $connect->prepare("DELETE FROM todos_db_rest WHERE id = $idOfTodo");
            $DeleteQuery->execute();
            $res = [
                'message' => 'Success',
                'status' => 200,
                'data' => $idOfTodo
            ];
            echo json_encode($res);
        }
        if ($method === "PUT" && $main_req === "forget_password") {
            $connect = $this->dbConnect();
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $email = $_GET['email'] ?? $queryParams['email'];
            $password = $_GET['newPassword'] ?? $queryParams['newPassword'];
            $conform_password = $_GET['conformNewPassword'] ?? $queryParams['conformNewPassword'];


            if ($password === $conform_password) {
                if (strlen($password) < 4) {
                    $res = [
                        'message' => 'Length of password id above 5',
                        'status' => 502

                    ];
                    echo json_encode($res);
                } else {
                    $encode_password = password_hash($password, PASSWORD_DEFAULT);

                    $select_data = $connect->prepare("SELECT * FROM user_login_ WHERE email = '$email'");
                    $select_data->execute();

                    $check_data_existing = count($select_data->fetchAll(PDO::FETCH_ASSOC));

                    if ($check_data_existing == 0) {
                        $res = [
                            'message' => 'Email error',
                            'status' => 402

                        ];
                        echo json_encode($res);

                    } else {
                        $updateQuery = $connect->prepare("UPDATE user_login_ SET password = '$encode_password' WHERE email = '$email'");
                        $updateQuery->execute();

                        $res = [
                            'message' => 'updating Success',
                            'status' => 200

                        ];
                        echo json_encode($res);
                    }

                }




            } else {
                $res = [
                    'message' => 'Password did not match!',
                    'status' => 200

                ];
                echo json_encode($res);

            }
        }
        if ($method === "GET" && $main_req === "isactive") {
            if (isset($_SESSION["id"])) {
                $res = [
                    'message' => 'Session Active',
                    'status' => 200,
                    "id" => $_SESSION["id"],
                    "name" => $_SESSION["name"]

                ];
                echo json_encode($res);
            } else {
                $res = [
                    'message' => 'Session Not Active',
                    'status' => 502

                ];
                echo json_encode($res);
            }
        }
        if ($method === "PUT" && $main_req === "addimg") {
            $connect = $this->dbConnect();
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $img_url = $_GET["imgSrc"] ?? $queryParams["imgSrc"];
            $task_id = $_GET["taskId"] ?? $queryParams["taskId"];
            $add_img_url = $connect->prepare("UPDATE todos_db_rest SET img_url = '$img_url' WHERE id = $task_id");
            $add_img_url->execute();

            $get_task = $connect->prepare("SELECT * from todos_db_rest WHERE id = $task_id");
            $get_task->execute();
            $result = $get_task->fetchAll(PDO::FETCH_ASSOC);

            $res = [
                'message' => 'Success',
                'status' => 200,
                'data' => $result
            ];
            echo json_encode($res);
        }
        if ($method === "POST" && $main_req === "setpic") {
            $connect = $this->dbConnect();
            $pic = $_REQUEST['user_pic'];
            $email = $_REQUEST['email'];
            $insetPic = $connect->prepare("UPDATE user_login_ SET user_pic = :pic WHERE email = :email");
            $insetPic->execute(array(':pic' => $pic, ':email' => $email));

            $get_data = $connect->prepare("SELECT * FROM user_login_ WHERE email = :email");
            $get_data->execute(array(':email' => $email));
            $row = $get_data->fetch();
            echo json_encode($row);
        }


    }


}

?>