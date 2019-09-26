<?php
session_start();
session_unset("usuario");
header("Location: ../");
?>