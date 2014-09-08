# Stats Library

Retrieve some simple application metrics between date ranges.  The `*_by_day()` methods retrieve the statistics in array with sequential days as the keys (perfect for charting with each day as a datapoint within the date range).

This class is primarily used in the control panel dashboard, but can be used application-wide.

## Initialization

```
$this->load->library('stats');
```

## Method Reference

[method]float revenue (date $date_start [, date $date_end = 'TODAY'])[/method]

Return total revenue within the date range.

```
$revenue = $this->stats->revenue('2010-11-01','2010-12-01');
```

[method]array revenue_by_day (date $date_start [, date $date_end = 'TODAY'])[/method]

Return an array of daily revenue figures between the date range.

[method]int orders (date $date_start [, date $date_end = 'TODAY'])[/method]

Return number of store orders within the date range.

```
$orders = $this->stats->orders('2010-11-01','2010-12-01');
```

[method]array orders_by_day (date $date_start [, date $date_end = 'TODAY'])[/method]

Return an array of daily order counts between the date range.

[method]int subscriptions (date $date_start [, date $date_end = 'TODAY'])[/method]

Return number of new subscriptions within the date range.

```
$subscriptions = $this->stats->subscriptions('2010-11-01','2010-12-01');
```

[method]array subscriptions_by_day (date $date_start [, date $date_end = 'TODAY'])[/method]

Return an array of daily new subscription counts between the date range.

[method]int registrations (date $date_start [, date $date_end = 'TODAY'])[/method]

Return number of new member registrations within the date range.

```
$signups = $this->stats->registrations('2010-11-01','2010-12-01');
```

[method]array registrations_by_day (date $date_start [, date $date_end = 'TODAY'])[/method]

Return an array of daily member registration counts between the date range.

[method]int logins (date $date_start [, date $date_end = 'TODAY'])[/method]

Return number of member logins within the date range.

```
$logins = $this->stats->logins('2010-11-01','2010-12-01');
```

[method]array logins_by_day (date $date_start [, date $date_end = 'TODAY'])[/method]

Return an array of daily login counts between the date range.