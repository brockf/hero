# Subscriptions

Retrieve and utilize the user's purchased subscriptions in your templates.

## Important URL's

* `/subscriptions`, by default, maps to a listing of subscription packages in the `subscriptions.thtml` template
* `/subscriptions/renew/OLD_SUBSCRIPTION_ID` adds a renewal subscription for a specific subscription ID related to the logged in user, redirects to checkout
* `/subscriptions/upgrade/OLD_SUBSCRIPTION_ID/NEW_PLAN_D` adds an upgrading subscription to a new plan for a specific subscription ID related to the logged in user, redirects to checkout

## Subscription Template Variables

Each subscription has the following available data accessible.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Variables</td>
		</tr>
		<tr>
			<td class="variable_name">Variable</td>
			<td class="variable_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>`{$id}`</td>
			<td>The subscription ID.</td>
		</tr>
		<tr>
			<td>`{$date_created}`</td>
			<td>The date the subscription was purchased.</td>
		</tr>
		<tr>
			<td>`{$amount}`</td>
			<td>The recurring charge, without a currency symbol.</td>
		</tr>
		<tr>
			<td>`{$interval}`</td>
			<td>The number of days between successive charges for this subscription.</td>
		</tr>
		<tr>
			<td>`{$start_date}`</td>
			<td>The date of the first charge.</td>
		</tr>
		<tr>
			<td>`{$end_date}`</td>
			<td>The date the subscription will/has expired.</td>
		</tr>
		<tr>
			<td>`{$last_charge_date}`</td>
			<td>The date of the last charge.</td>
		</tr>
		<tr>
			<td>`{$next_charge_date}`</td>
			<td>The date of the next charge.</td>
		</tr>
		<tr>
			<td>`{$number_occurrences}`</td>
			<td>The number of charges that have/will occur for this subscription.</td>
		</tr>
		<tr>
			<td>`{$card_last_four}`</td>
			<td>If the user entered a credit card, the last 4 digits of this credit card.</td>
		</tr>
		<tr>
			<td>`{$cancel_date}`</td>
			<td>The date the subscription was cancelled, if applicable.</td>
		</tr>
		<tr>
			<td>`{$plan.name}`</td>
			<td>The name of the subscription plan (notice it's an array)</td>
		</tr>
		<tr>
			<td>`{$renew_link}`</td>
			<td>An absolute URL for the renewal link.</td>
		</tr>
		<tr>
			<td>`{$cancel_link}`</td>
			<td>An absolute URL to cancel this subscription.</td>
		</tr>
		<tr>
			<td>`{$update_cc_link}`</td>
			<td>An absolute URL to update the billing information for this subscription.</td>
		</tr>
		<tr>
			<td>`{$is_recurring}`</td>
			<td>Set to TRUE if this subscription is actively recurring, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$is_active}`</td>
			<td>Set to TRUE if this subscription has not expired, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$is_renewed}`</td>
			<td>Set to TRUE if this subscription has been subsequently renewed by the user, else FALSE.</td>
		</tr>
	</tbody>
</table>

## Subscription Plan Template Variables

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Variables</td>
		</tr>
		<tr>
			<td class="variable_name">Variable</td>
			<td class="variable_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>`{$id}`</td>
			<td>The subscription plan ID.</td>
		</tr>
		<tr>
			<td>`{$name}`</td>
			<td>The name of the plan.</td>
		</tr>
		<tr>
			<td>`{$type}`</td>
			<td>Either "paid" or "free".</td>
		</tr>
		<tr>
			<td>`{$initial_charge}`</td>
			<td>The initial charge for the subscription.  Does not co-exist with a free trial.</td>
		</tr>
		<tr>
			<td>`{$amount}`</td>
			<td>The recurring charge for the subscription.</td>
		</tr>
		<tr>
			<td>`{$interval}`</td>
			<td>The number of days between recurring charges.</td>
		</tr>
		<tr>
			<td>`{$free_trial}`</td>
			<td>The number of days prior to the first charge.</td>
		</tr>
		<tr>
			<td>`{$occurrences}`</td>
			<td>The number of occurrences for the subscription (if not unlimited).</td>
		</tr>
		<tr>
			<td>`{$number_occurrences}`</td>
			<td>The number of charges that have/will occur for this subscription.</td>
		</tr>
		<tr>
			<td>`{$is_taxable}`</td>
			<td>Set to TRUE/FALSE depending on if the subscription plan is charged exempt.</td>
		</tr>
		<tr>
			<td>`{$active_subscribers}`</td>
			<td>The number of members currently subscribing to the plan.</td>
		</tr>
		<tr>
			<td>`{$require_billing_for_trial}`</td>
			<td>Set to TRUE/FALSE depending of if billing information is required for the free trial.</td>
		</tr>
		<tr>
			<td>`{$description}`</td>
			<td>The plan description.</td>
		</tr>
		<tr>
			<td>`{$promotion}`</td>
			<td>If set, this is the member group that subscribers are added to.</td>
		</tr>
		<tr>
			<td>`{$demotion}`</td>
			<td>If set, this is the member group that expired subscribers are added to.</td>
		</tr>
		<tr>
			<td>`{$add_to_cart}`</td>
			<td>If this URL is accessed, the subscription is added to the member's cart and they are redirected to the cart.  Use it in a [b]subscribe now[/b] link.</td>
		</tr>
	</tbody>
</table>

## Template Plugins

[tag]{has_subscriptions}[/tag]

Only display the content between the two tags if the user has at least one active subscription on their account.

Usage:

```
{has_subscriptions}
You are a subscriber (of some kind)!
{/has_subscriptions}
```

[tag]{subscriptions}[/tag]

Retrieve the user's subscriptions (active or expired) based on a set of parameters.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Parameters</td>
		</tr>
		<tr>
			<td class="parameter_name">Variable</td>
			<td class="is_required">Required?</td>
			<td class="parameter_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>var</td>
			<td>Required</td>
			<td>Specify the name for the returned variable array (e.g., "sub" returns an array with keys like `{$sub.date_created}`.</td>
		</tr>
		<tr>
			<td>active</td>
			<td>No</td>
			<td>Set to TRUE to return only subscriptions that have not expired.</td>
		</tr>
		<tr>
			<td>recurring</td>
			<td>No</td>
			<td>Set to TRUE to return only subscriptions that are still actively recurring.</td>
		</tr>
		<tr>
			<td>id</td>
			<td>No</td>
			<td>Specify a particular subscription to return.</td>
		</tr>
		<tr>
			<td>plan_id</td>
			<td>No</td>
			<td>Only return subscriptions to this particular subscription plan.</td>
		</tr>
	</tbody>
</table>

Example usage (from the account manager):

```
{has_subscriptions}
<table class="table" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<td colspan="2">Your Subscriptions</td>
		</tr>
	</thead>
	<tbody>
	{subscriptions var="sub" active=TRUE}
	{assign var="sub_id" value=$sub.id}
		<tr>
			<td style="width:50%"><b>{$sub.plan.name}</b></td>
			<td>
				{if $sub.is_recurring == TRUE}Next Charge: {$sub.next_charge_date|date_format:"%B %e, %Y"}
				{else}Expires: {$sub.end_date|date_format:"%B %e, %Y"}{/if}
				{if $sub.is_renewed == TRUE} (Renewed){/if}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<ul class="sub_options">
					{if $sub.card_last_four and $sub.is_recurring}
						<li>
							<a href="{$sub.update_cc_link}">Update Credit Card Information</a>
						</li>
					{/if}
					{if $sub.last_charge_date}
						<li>
							<a href="{url path="users/invoices/$sub_id"}">View Related Invoices</a>
						</li>
					{/if}
					{if $sub.is_recurring}
						<li>
							<a href="{$sub.cancel_link}">Cancel Subscription</a>
						</li>
					{/if}
					{if $sub.is_renewed == FALSE and $sub.last_charge_date}
						<li>
							<a href="{$sub.renew_link}">Renew Subscription</a>
						</li>
					{/if}
					
					{if !$sub.is_recurring and !$sub.last_charge_date}
						<li>No options available.</li>
					{/if}
				</ul>
			</td>
		</tr>
	{/subscriptions}
	</tbody>
</table>
{/has_subscriptions}
```

[tag]{subscription_plans}[/tag]

Retrieve and display your site's subscription plan products.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Parameters</td>
		</tr>
		<tr>
			<td class="parameter_name">Variable</td>
			<td class="is_required">Required?</td>
			<td class="parameter_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>var</td>
			<td>Required</td>
			<td>Specify the name for the returned variable array (e.g., "sub" returns an array with keys like `{$sub.name}`.</td>
		</tr>
		<tr>
			<td>id</td>
			<td>No</td>
			<td>Specify a particular subscription plan to return.</td>
		</tr>
	</tbody>
</table>

Example usage:

```
<h2>Check out our available subscription products!</h2>

<table class="table" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<td colspan="2">Your Subscriptions</td>
		</tr>
	</thead>
	<tbody>
	{subscription_plans var="plan"}
		<tr>
			<td style="width:50%"><b>{$plan.name}</b></td>
			<td>
				{setting name="currency_symbol"}{money_format value=$plan.amount} {if $plan.free_trial}{$plan.free_trial} day free trial{/if}
			</td>
			<td>
				<a href="{$plan.add_to_cart}">subscribe now!</a>
			</td>
		</tr>
	{/subscription_plans}
	</tbody>
</table>
```