<?php
/**
 * Crear la Base de Dades to-do si no existeix
 */
try {

// starts the connection to the database
$password = "";
$user = "root";
$dbh = new PDO("mysql:host=localhost", $user, $password);
// set the PDO error mode to exception
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Error: " .$e->getMessage();
}
       
       try{
        $dbh->exec("
                            SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
                            SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
                            SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
                            
                            CREATE SCHEMA IF NOT EXISTS `to-do` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin ;
                            USE `to-do` ;
                            
                            CREATE TABLE IF NOT EXISTS `to-do`.`users` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT,
                            `user` VARCHAR(15) NOT NULL,
                            `password` VARCHAR(255) NOT NULL,
                            `name` VARCHAR(45) NOT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE INDEX `username_UNIQUE` (`user` ASC) )
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 4
                            DEFAULT CHARACTER SET = utf8
                            COLLATE = utf8_bin;
                            
                            CREATE TABLE IF NOT EXISTS `to-do`.`tasks` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT,
                            `timestampStart` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                            `timestampEnd` DATETIME NULL DEFAULT NULL,
                            `task` MEDIUMTEXT NOT NULL,
                            `status` ENUM('Pending', 'In progress', 'Finished') NOT NULL DEFAULT 'Pending' COMMENT 'Els noms en anglès coincidiràn amb els de l\'aplicació!',
                            `user_id` INT(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            INDEX `fk_Tasks_Users_idx` (`user_id` ASC) ,
                            CONSTRAINT `fk_Tasks_Users`
                                FOREIGN KEY (`user_id`)
                                REFERENCES `to-do`.`users` (`id`)
                                ON DELETE CASCADE
                                ON UPDATE CASCADE)
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 19
                            DEFAULT CHARACTER SET = utf8
                            COLLATE = utf8_bin;
                            
                            SET SQL_MODE=@OLD_SQL_MODE;
                            SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
                            SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS");

       } catch(PDOException $e){
           echo "Error al crear la Base de Dades<br>" . $e->getMessage();
       }
$dbh = null;

//omplir la taula users si és buida -> nova conexió, aquest cop a la BD to-do... hard coded :(
try {

  $dbh = new PDO("mysql:host=localhost; dbname=to-do", $user, $password);

  $result = $dbh->query("select * from users");

  if ($result->rowCount() == 0){

            $sql = "INSERT INTO users (id, user, password, name) 
                    VALUES ('1', 'agarcia', 'agarcia123', 'Albert'),
                          ('2', 'ralcalde', 'ralcalde123', 'Rubén'),
                          ('3', 'jvila', 'jvila123', 'Joan')";
            $dbh->exec($sql);
  } 
} catch (PDOException $e){
  echo "Error al fer l'INSERT a la taula users" . $e;
}

$dbh = null;

?>