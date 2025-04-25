<title>E-Commerce</title>

<link rel="stylesheet" href="{{asset('frontend/css/style.css')}}">
<link rel="stylesheet" href="{{asset('frontend/css/themify-icons.css')}}">
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

.card_box {
    display: flex;
    flex-direction: column;
    width: 40%;
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    margin-top: 20px;
}

.card_input {
    width: 100%;
    box-sizing: border-box;
    border: #ddd solid 1px;
    border-radius: 6px;
    color: #555;
    padding: 8px;
    margin-top: 2px;
    margin-bottom: 14px;
    outline: none;
    font-family: inherit;
}

#card-button {
    width: 100%;
    background-color: #7dc885;
    border: #7dc885 solid 2px;
    border-radius: 6px;
    color: #fff;
    margin-top: 8px;
    padding: 8px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
}

#card-button:hover {
    opacity: .8;
}

#card-result {
    color: #444;
    font-weight: bold;
}

#card-number-element {
    background-image: url('images/unknown.png');
    background-repeat: no-repeat;
    background-position: right center;
    background-size: 24px;
    background-origin: content-box;
}

#card-cvc-element {
    background-image: url('images/cvc.png');
    background-repeat: no-repeat;
    background-position: right center;
    background-size: 24px;
    background-origin: content-box;
}

.card_row {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    gap: 10px;
}

.card_row > div {
    width: 100%;
}
</style>   