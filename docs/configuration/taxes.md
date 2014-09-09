# Taxes

Taxes can be configured at *Storefront > Tax Rules* in the control panel.

Taxes are configured as a set of *tax rules* consisting of either a *State/Province* or *Country*, a *Tax Rate (%)*, and the *Tax Name*.

When a visitor is checking out with either products or a subscription, they will be matched against these tax rules.  They can only match one tax rule at a time, with a State/Province match taking precedence over a Country match.  For example, if you have a tax rule specifying users from Canada should be taxed at 12% and another tax rule specifying that visitors from Ontario, Canada should be taxed at 15%, someone from Ontario will be taxed at 15% because there was a State/Province match.  Anyone else from Canada will be taxed at 12%.

The tax rate and name will be displayed to your users upon checking out, and will be be tracked in each individual invoice.

When it comes time for your business to remit your taxes, you can easily [generate paid tax reports](/docs/configuration/reports.md) for a specific date range.

