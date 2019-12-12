<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <form action="/register" method="post">
        @csrf
        <br>
        <br>
        {{'Fullname'}}
        <input type="text" name="fullname" id="">
        <br>
        {{'Username'}}
        <input type="text" name="username">
        <br>
        {{'Password'}}
        <input type="password" name="password">
        <br>
        {{'Date of Birth'}}
        <input type="date" name="DOB">
        <br>
        {{'Address'}}
        <textarea name="address" id="" cols="30" rows="10"></textarea>
        <br>
        {{'Sex'}}
        <div class="control">
            <label class="radio">
                <input type="radio" name="sex" value="0">
                Male
            </label>
            <label class="radio">
                <input type="radio" name="sex" value="1">
                Female
            </label>
        </div>
        <br>
        {{'Email'}}
        <input type="email" name="email">
        <br>
        {{'Phone'}}
        <input type="number" name="phone">
        <input type="submit" value="Login">
    </form>

</body>

</html>
