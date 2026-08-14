<!DOCTYPE html>
<html>
        <head>
                <title>About Me-Dovskyi</title>
        </head>
        <body>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <script src="/libs/snow/snow.js"></script>
                <script>
                        snow.start({
                                flakeCount: 1500,
                                stickingRatio: 0.4,
                                color: "#C4C4C4", 
                                wind: 15,
                        });
                </script>

                <div class="container">
                <h2>This page will be published on New Year 2027</h2>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/utils/stub.php'; ?>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
