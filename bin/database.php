<?php
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "if0_43480766_amp_store";

    $koneksi = mysqli_connect($host,$user,$password,$database);

    if(!$koneksi){
        echo "koneksi gagal!";
    }
?>
