<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Invoice Nr: </title>
    <style>
        body {
            padding: 30px;
        }
        table tbody tr th {
            width: 50%;
        }
        .container-fluid {
            width:100%;
            padding-right:var(--bs-gutter-x,.75rem);
            padding-left:var(--bs-gutter-x,.75rem);
            margin-right:auto;
            margin-left:auto;
        }
        .mt-5 {
            margin-top:3rem!important;
        }
        table {
            caption-side:bottom;
            border-collapse:collapse;
        }
        table {
            display:table!important;
        }
        .table {
            --bs-table-bg:transparent;
            --bs-table-accent-bg:transparent;
            --bs-table-striped-color:#212529;
            --bs-table-striped-bg:#f2f2f2;
            --bs-table-active-color:#212529;
            --bs-table-active-bg:rgba(0, 0, 0, 0.1);
            --bs-table-hover-color:#212529;
            --bs-table-hover-bg:rgba(0, 0, 0, 0.075);
            width:100%;
            margin-bottom:1rem;
            color:#212529;
            vertical-align:top;
            border-color:#dee2e6;
        }
        .table>*>*>* {
            padding:.5rem .5rem;
            border-bottom-width:1px;
            box-shadow:inset 0 0 0 9999px var(--bs-table-accent-bg);
        }
        .row {
            --bs-gutter-x:1.5rem;
            --bs-gutter-y:0;
            display:flex;
            flex-wrap:wrap;
            margin-top:calc(var(--bs-gutter-y) * -1);
            margin-right:calc(var(--bs-gutter-x) * -.5);
            margin-left:calc(var(--bs-gutter-x) * -.5);
        }
        .row>* {
            flex-shrink:0;
            width:100%;
            max-width:100%;
            padding-right:calc(var(--bs-gutter-x) * .5);
            padding-left:calc(var(--bs-gutter-x) * .5);
            margin-top:var(--bs-gutter-y);
        }
        .text-end {
            text-align:right!important;
        }
        .col {
            flex:1 0 0%;
        }
        .table-striped tbody tr:nth-child(odd) {
            background-color: #f2f2f2; /* 條紋背景色 */
        }
        .table-striped thead, .table-striped tbody {
            border-bottom: 1.1px solid #333 !important;
        }
        tr {
            border-bottom: 1px solid #dee2e6;
        }
        th {
            text-align:inherit;
            text-align:-webkit-match-parent;
        }
        :root{ --bs-blue:#0d6efd;
            --bs-indigo:#6610f2;
            --bs-purple:#6f42c1;
            --bs-pink:#d63384;
            --bs-red:#dc3545;
            --bs-orange:#fd7e14;
            --bs-yellow:#ffc107;
            --bs-green:#198754;
            --bs-teal:#20c997;
            --bs-cyan:#0dcaf0;
            --bs-white:#fff;
            --bs-gray:#6c757d;
            --bs-gray-dark:#343a40;
            --bs-primary:#0d6efd;
            --bs-secondary:#6c757d;
            --bs-success:#198754;
            --bs-info:#0dcaf0;
            --bs-warning:#ffc107;
            --bs-danger:#dc3545;
            --bs-light:#f8f9fa;
            --bs-dark:#212529;
            --bs-font-sans-serif:"notosanstc",system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji","noto sans tc";
            --bs-font-monospace:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
            --bs-gradient:linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0))
        }
        *,::after,::before {
            box-sizing:border-box;
        }
        body {
            margin:0;
            font-family:var(--bs-font-sans-serif);
            font-size:1rem;
            font-weight:400;
            line-height:1.5;
            color:#212529;
            background-color:#fff;
            -webkit-text-size-adjust:100%;
            -webkit-tap-highlight-color:transparent;
        }
        .h1,.h2,.h3,.h4,.h5,.h6,h1,h2,h3,h4,h5,h6 {
            margin-top:0;
            margin-bottom:.5rem;
            font-weight:500;
            line-height:1.2;
        }
        .h1,h1 {
            font-size:calc(1.375rem + 1.5vw);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1 class="mt-5">Invoice</h1>
        <table class="table mt-5">
            <tbody>
                <tr>
                    <th scope="row">Number</th>
                    <td>{{$order->order_number}}</td>
                </tr>
                <tr>
                    <th scope="row">Invoice to</th>
                    <td>{{$order->name}}</td>
                </tr>
                <tr>
                    <th scope="row">Order Date</th>
                    <td>{{$order->created_at->format('DdmY')}}</td>
                </tr>
                <tr>
                    <th scope="row">Address</th>
                    <td>{{$order->address}}</td>
                </tr>
                <tr>
                    <th scope="row">Phone</th>
                    <td>{{$order->phone}}</td>
                </tr>
            </tbody>
        </table>
        <table class="table table-striped mt-5">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order['order_details'] as $detail)
                    <tr>
                        <td>{{$detail->title}}</td>
                        <td>{{$detail->quantity}}</td>
                        <td>${{$detail->amount}}</td>
                        <td>${{$detail->quantity*$detail->amount}}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">
                        Sub Total
                    </th>
                    <td>
                        ${{$order->sub_total}}
                    </td>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">
                        Shipping
                    </th>
                    <td>
                        ${{$order->shipping_fee}}
                    </td>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">
                        Total
                    </th>
                    <td>
                        ${{$order->total_amount}}
                    </td>
                </tr>
            </tfoot>
        </table>
        <div class="row mt-5">
            <div class="col">
                <small>
                    <strong>Notes:</strong><br/>
                        {{$order->note}}
                </small>
            </div>
        </div>
        <hr/>
    </div>
</body>
</html>