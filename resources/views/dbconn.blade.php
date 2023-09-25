<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel JWT Auth</title>
    </head>
    <body>
        <div>
            <?php 
            
                if (DB::connection()->getPdo()) {
                    echo "Successfully connected DB " . DB::connection()->getDatabaseName();
                }
            
            ?>
        </div>
    </body>
</html>