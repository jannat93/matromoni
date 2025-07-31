<?php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   ?>

   <!DOCTYPE html>
   <html lang="en">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>Success</title>
       <style>
           body {
               font-family: Arial, sans-serif;
               display: flex;
               justify-content: center;
               align-items: center;
               height: 100vh;
               margin: 0;
               background-color: #f4f4f4;
           }
           .success-message {
               text-align: center;
               padding: 20px;
               background-color: #dff0d8;
               border: 2px solid #3c763d;
               border-radius: 5px;
               color: #3c763d;
           }
           a {
               display: block;
               margin-top: 10px;
               color: #337ab7;
               text-decoration: none;
           }
           a:hover {
               text-decoration: underline;
           }
       </style>
   </head>
   <body>
       <div class="success-message">
           <p>Your message has been sent successfully!</p>
           <a href="javascript:history.back()">Back</a>
       </div>
   </body>
   </html>