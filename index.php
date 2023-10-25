<?php

include  'lemsi.php';

lemsi::connect('localhost', 'root', 'root', 'orm');

$data = lemsi::select('users');                             //select table in database

$data->name = 'John Doe';                                   //column name
$data->email = 'john.doeфыasdasdв@example.com';             //column email
$data->password = 'password123';                            //column password
$data->age = 1;                                             //column age     (in db width INT type)         

lemsi::save($data);                                         //store changes

lemsi::select('users');                                     //select table in database
$user = lemsi::find(1);                                     //find with id = 1
print_r($user);                                             //print

$data = lemsi::select('users');                             //select table in database
$data->name = 'alex';                                       //set name = 'alex'
lemsi::update(1, $data);                                    //store changes

$data = lemsi::select('users');                             //select table in database
lemsi::delete(1);                                           //delete with id = 1