$(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/cart-update",
        method: "post",
        dataType: "json",
        data: {
            product_id: fieldName,
            quantity: currentVal,
            amount: amountVal
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('提交失敗，請重試。');
        }
    });

    $('.table-cart .qtyplus').click(function(e) {
        e.preventDefault();
        fieldName = $(this).attr('field');
        var currentVal = parseInt($('input[name=' + fieldName + ']').val());
        var stock = $('input[name=' + fieldName + ']').data('stock');
        var quantityVal = $('input[name=' + fieldName + ']').data('quantity');
        var price = $('input[name=' + fieldName + ']').data('price');
        var amountVal = quantityVal*price;
        if (!isNaN(currentVal) && currentVal < stock) {
            $('input[name=' + fieldName + ']').val(++currentVal);
            $('#' + fieldName ).text('$' + (currentVal*price));
            amountVal = currentVal*price;
        } else if (!isNaN(currentVal) && currentVal > stock) {
            $('input[name=' + fieldName + ']').val(stock);
            $('#' + fieldName ).text('$' + (stock*price));
            currentVal = stock;
            amountVal = stock*price;
        }
        $.ajax();
    });
    $(".table-cart .qtyminus").click(function(e) {
        e.preventDefault();
        fieldName = $(this).attr('field');
        var currentVal = parseInt($('input[name=' + fieldName + ']').val());
        var stock = $('input[name=' + fieldName + ']').data('stock');
        var quantityVal = $('input[name=' + fieldName + ']').data('quantity');
        var price = $('input[name=' + fieldName + ']').data('price');
        var amountVal = quantityVal*price;
        if (!isNaN(currentVal) && currentVal > 1) {
            $('input[name=' + fieldName + ']').val(--currentVal);
            $('#' + fieldName ).text('$' + (currentVal*price));
            amountVal = currentVal*price;
        } else {
            $('input[name=' + fieldName + ']').val(1);
            $('#' + fieldName ).text('$' + (1*price));
            currentVal = 1;
            amountVal = 1*price;
        }
        $.ajax();
    });
    $(".table-cart .qty").blur(function() {
        fieldName = $(this).attr('field');
        var currentVal = parseInt($(this).val());
        var stock = $('input[name=' + fieldName + ']').data('stock');
        var quantityVal = $('input[name=' + fieldName + ']').data('quantity');
        var price = $('input[name=' + fieldName + ']').data('price');
        var amountVal = quantityVal*price;
        if (!isNaN(currentVal) && currentVal > stock) {
            $('input[name=' + fieldName + ']').val(stock);
            $('#' + fieldName ).text('$' + (stock*price));
            currentVal = stock;
            amountVal = stock*price;
        }
        else if (currentVal >= 1  && currentVal <= stock) {
            $('#' + fieldName ).text('$' + (currentVal*price));
            amountVal = currentVal*price;
        }
        else if (currentVal < 1) {
            $('input[name=' + fieldName + ']').val(1);
            $('#' + fieldName ).text('$' + (1*price));
            currentVal = 1;
            amountVal = 1*price;
        }
        else if (isNaN(currentVal)) {
            $('input[name=' + fieldName + ']').val(1);
            $('#' + fieldName ).text('$' + (1*price));
            currentVal = 1;
            amountVal = 1*price;
        }
        $.ajax();
    });
});