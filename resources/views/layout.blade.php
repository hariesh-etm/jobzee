<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Jobzee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?=url('/')?>/assets/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.css" integrity="sha512-8BU3emz11z9iF75b10oPjjpamM4Mz23yQFQymbtwyPN3mNWHxpgeqyrYnkIUP6A8KyAj5k2p3MiYLtYqew7gIw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body>
@include('topbar')

@yield('content')

@include('footer')

<script>
    window.onscroll = function() {
        myFunction()
    };

    var navbar = document.getElementById("navbar");
    var sticky = navbar.offsetTop;

    function myFunction() {
        if (window.pageYOffset >= sticky) {
            navbar.classList.add("sticky")
        } else {
            navbar.classList.remove("sticky");
        }
    }
</script>
{{-- <script src="https://kit.fontawesome.com/51d697c1d0.js" crossorigin="anonymous"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.js" integrity="sha512-5FvlLK9/1zFkolsL3agoJESBOWEd1gEzY1/Ul5Qw9cTMiPf/tiZoFD10DB9rQLK/rBBUfXECkG6ZaV7RXkcWNw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html>
