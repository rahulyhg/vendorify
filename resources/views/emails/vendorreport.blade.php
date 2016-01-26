<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body style="font-family: arial; color: #333333;"> 
        <table>
            <tr>
                <td style="padding: 0 20px 10px 0;">
                    <img src="{{ asset('img/logo.png') }}" width="125px" />
                </td>
                <td>
                    <h1>{{ $report['vendor']['business'] }}</h1>
                    <h2 style="margin-top: -20px;">Sales Summary for {{$start_date}} to {{$end_date}}</h2>
                </td>
            </tr>
        </table>
        @if(trim($report['message']) != '')
        <p style="padding: 20px;">
            {!! preg_replace('/\n|\r\n/', '<br />', $report['message']) !!}
        </p>
        @endif
        <hr />
        <table>
            <thead style="text-align: left;">
                <tr>
                    <th style="padding: 5px 10px;">Date</th>
                    <th style="padding: 5px 10px;">Quantity</th>
                    <th style="padding: 5px 10px;">Description</th>
                    <th style="padding: 5px 10px;">Gross</th>
                    <th style="padding: 5px 10px;">Discounts</th>
                    <th style="padding: 5px 10px;">Net</th>
                    <th style="padding: 5px 10px;">Commision Fee</th>
                    <th style="padding: 5px 10px;">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($report['transactions'] as $transaction)
                <tr{{$transaction['custom'] ? ' style=color:#00acac;' : '' }}>
                    <td style="padding: 5px 10px;">{{ date('m/d/Y', strtotime($transaction['processed_at'])) }}</td>
                    <td style="padding: 5px 10px;">{{$transaction['quantity']}}</td>
                    <td style="padding: 5px 10px;">{{$transaction['description']}} {{$transaction['refund'] ? '(refunded)' : '' }}</td>
                    <td style="padding: 5px 10px;">{{$transaction['gross']}}</td>
                    <td style="padding: 5px 10px;">{{$transaction['discounts']}}</td>
                    <td style="padding: 5px 10px;">{{$transaction['net']}}</td>
                    <td style="padding: 5px 10px;">{{round($transaction['commision']*-1,2)}}</td>
                    <td style="padding: 5px 10px;">{{round($transaction['total'], 2)}}</td>
                </tr>
            @endforeach
            @if(!count($report['transactions']))
                <tr>
                    <td colspan="8" style="padding: 20px 10px; text-align:center;">No transactions for {{$start_date}} - {{$end_date}}</td>
                </td>
            @endif
            </tbody>
            <tfoot>
                <tr>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px; border-top: 1px solid #eeeeee;"><strong>Total:</strong></td>
                    <td style="padding: 5px 10px; border-top: 1px solid #eeeeee;"><strong>{{round($report['net'],2)}}</strong></td>
                    <td style="padding: 5px 10px; border-top: 1px solid #eeeeee;"><strong>-{{round($report['commision'],2)}} ({{$report['vendor']['rate']}}%)</strong></td>
                    <td style="padding: 5px 10px; border-top: 1px solid #eeeeee;"><strong>{{round($report['total'],2)}}</strong></td>
                </tr>
                @if ($include_rent)
                <tr>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"><strong>Rent Owed:</strong></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"><strong>-{{round($report['vendor']['rent'],2)}}</strong></td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"><strong>{{ ($report['payout'] < 0) ? 'Amount Due:' : 'Check Total' }}</strong></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"></td>
                    <td style="padding: 5px 10px;"><strong>{{ ($report['payout'] < 0) ? round($report['payout']*-1, 2) :  round($report['payout'],2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
        <hr />
        <p style="margin: 20px 0 0 20px;">
            <em>This report is your paperless statement for this pay period.<br />
            Please download the attached csv report for your records and tax purposes.</em>
        </p>
        <p style="margin-left: 20px">
            As always, thank you for choosing {{env('APP_BUSINESS')}} for your store!<br />
            <span style="margin-left: 20px;">- {{env('APP_NAME')}}</span>
        <p>
    </body>
</html>