<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

-- 
-- Table structure for table `countries`
-- 

CREATE TABLE `countries` (
  `country_id` int(11) NOT NULL,
  `iso2` varchar(2) NOT NULL,
  `iso3` varchar(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `countries`
-- 

INSERT INTO `countries` (`country_id`, `iso2`, `iso3`, `name`) VALUES (4, 'AF', 'AFG', 'Afghanistan'),
(248, 'AX', 'ALA', 'Aland Islands'),
(8, 'AL', 'ALB', 'Albania'),
(12, 'DZ', 'DZA', 'Algeria'),
(16, 'AS', 'ASM', 'American Samoa'),
(20, 'AD', 'AND', 'Andorra'),
(24, 'AO', 'AGO', 'Angola'),
(660, 'AI', 'AIA', 'Anguilla'),
(10, 'AQ', 'ATA', 'Antarctica'),
(28, 'AG', 'ATG', 'Antigua and Barbuda'),
(32, 'AR', 'ARG', 'Argentina'),
(51, 'AM', 'ARM', 'Armenia'),
(533, 'AW', 'ABW', 'Aruba'),
(36, 'AU', 'AUS', 'Australia'),
(40, 'AT', 'AUT', 'Austria'),
(31, 'AZ', 'AZE', 'Azerbaijan'),
(44, 'BS', 'BHS', 'Bahamas'),
(48, 'BH', 'BHR', 'Bahrain'),
(50, 'BD', 'BGD', 'Bangladesh'),
(52, 'BB', 'BRB', 'Barbados'),
(112, 'BY', 'BLR', 'Belarus'),
(56, 'BE', 'BEL', 'Belgium'),
(84, 'BZ', 'BLZ', 'Belize'),
(204, 'BJ', 'BEN', 'Benin'),
(60, 'BM', 'BMU', 'Bermuda'),
(64, 'BT', 'BTN', 'Bhutan'),
(68, 'BO', 'BOL', 'Bolivia'),
(70, 'BA', 'BIH', 'Bosnia and Herzegovina'),
(72, 'BW', 'BWA', 'Botswana'),
(74, 'BV', 'BVT', 'Bouvet Island'),
(76, 'BR', 'BRA', 'Brazil'),
(86, 'IO', 'IOT', 'British Indian Ocean Territory'),
(96, 'BN', 'BRN', 'Brunei Darussalam'),
(100, 'BG', 'BGR', 'Bulgaria'),
(854, 'BF', 'BFA', 'Burkina Faso'),
(108, 'BI', 'BDI', 'Burundi'),
(116, 'KH', 'KHM', 'Cambodia'),
(120, 'CM', 'CMR', 'Cameroon'),
(124, 'CA', 'CAN', 'Canada'),
(132, 'CV', 'CPV', 'Cape Verde'),
(136, 'KY', 'CYM', 'Cayman Islands'),
(140, 'CF', 'CAF', 'Central African Republic'),
(148, 'TD', 'TCD', 'Chad'),
(152, 'CL', 'CHL', 'Chile'),
(156, 'CN', 'CHN', 'China'),
(162, 'CX', 'CXR', 'Christmas Island'),
(166, 'CC', 'CCK', 'Cocos (Keeling) Islands'),
(170, 'CO', 'COL', 'Colombia'),
(174, 'KM', 'COM', 'Comoros'),
(178, 'CG', 'COG', 'Congo'),
(180, 'CD', 'COD', 'Congo, Democratic Republic of the'),
(184, 'CK', 'COK', 'Cook Islands'),
(188, 'CR', 'CRI', 'Costa Rica'),
(384, 'CI', 'CIV', 'Côte d''Ivoire'),
(191, 'HR', 'HRV', 'Croatia'),
(192, 'CU', 'CUB', 'Cuba'),
(196, 'CY', 'CYP', 'Cyprus'),
(203, 'CZ', 'CZE', 'Czech Republic'),
(208, 'DK', 'DNK', 'Denmark'),
(262, 'DJ', 'DJI', 'Djibouti'),
(212, 'DM', 'DMA', 'Dominica'),
(214, 'DO', 'DOM', 'Dominican Republic'),
(218, 'EC', 'ECU', 'Ecuador'),
(818, 'EG', 'EGY', 'Egypt'),
(222, 'SV', 'SLV', 'El Salvador'),
(226, 'GQ', 'GNQ', 'Equatorial Guinea'),
(232, 'ER', 'ERI', 'Eritrea'),
(233, 'EE', 'EST', 'Estonia'),
(231, 'ET', 'ETH', 'Ethiopia'),
(238, 'FK', 'FLK', 'Falkland Islands (Malvinas)'),
(234, 'FO', 'FRO', 'Faroe Islands'),
(242, 'FJ', 'FJI', 'Fiji'),
(246, 'FI', 'FIN', 'Finland'),
(250, 'FR', 'FRA', 'France'),
(254, 'GF', 'GUF', 'French Guiana'),
(258, 'PF', 'PYF', 'French Polynesia'),
(260, 'TF', 'ATF', 'French Southern Territories'),
(266, 'GA', 'GAB', 'Gabon'),
(270, 'GM', 'GMB', 'Gambia'),
(268, 'GE', 'GEO', 'Georgia'),
(276, 'DE', 'DEU', 'Germany'),
(288, 'GH', 'GHA', 'Ghana'),
(292, 'GI', 'GIB', 'Gibraltar'),
(300, 'GR', 'GRC', 'Greece'),
(304, 'GL', 'GRL', 'Greenland'),
(308, 'GD', 'GRD', 'Grenada'),
(312, 'GP', 'GLP', 'Guadeloupe'),
(316, 'GU', 'GUM', 'Guam'),
(320, 'GT', 'GTM', 'Guatemala'),
(831, 'GG', 'GGY', 'Guernsey'),
(324, 'GN', 'GIN', 'Guinea'),
(624, 'GW', 'GNB', 'Guinea-Bissau'),
(328, 'GY', 'GUY', 'Guyana'),
(332, 'HT', 'HTI', 'Haiti'),
(334, 'HM', 'HMD', 'Heard Island and McDonald Islands'),
(336, 'VA', 'VAT', 'Holy See (Vatican City State)'),
(340, 'HN', 'HND', 'Honduras'),
(344, 'HK', 'HKG', 'Hong Kong'),
(348, 'HU', 'HUN', 'Hungary'),
(352, 'IS', 'ISL', 'Iceland'),
(356, 'IN', 'IND', 'India'),
(360, 'ID', 'IDN', 'Indonesia'),
(364, 'IR', 'IRN', 'Iran, Islamic Republic of'),
(368, 'IQ', 'IRQ', 'Iraq'),
(372, 'IE', 'IRL', 'Ireland'),
(833, 'IM', 'IMN', 'Isle of Man'),
(376, 'IL', 'ISR', 'Israel'),
(380, 'IT', 'ITA', 'Italy'),
(388, 'JM', 'JAM', 'Jamaica'),
(392, 'JP', 'JPN', 'Japan'),
(832, 'JE', 'JEY', 'Jersey'),
(400, 'JO', 'JOR', 'Jordan'),
(398, 'KZ', 'KAZ', 'Kazakhstan'),
(404, 'KE', 'KEN', 'Kenya'),
(296, 'KI', 'KIR', 'Kiribati'),
(408, 'KP', 'PRK', 'Korea, Democratic People''s Republic of'),
(410, 'KR', 'KOR', 'Korea, Republic of'),
(414, 'KW', 'KWT', 'Kuwait'),
(417, 'KG', 'KGZ', 'Kyrgyzstan'),
(418, 'LA', 'LAO', 'Lao People''s Democratic Republic'),
(428, 'LV', 'LVA', 'Latvia'),
(422, 'LB', 'LBN', 'Lebanon'),
(426, 'LS', 'LSO', 'Lesotho'),
(430, 'LR', 'LBR', 'Liberia'),
(434, 'LY', 'LBY', 'Libyan Arab Jamahiriya'),
(438, 'LI', 'LIE', 'Liechtenstein'),
(440, 'LT', 'LTU', 'Lithuania'),
(442, 'LU', 'LUX', 'Luxembourg'),
(446, 'MO', 'MAC', 'Macao'),
(807, 'MK', 'MKD', 'Macedonia, the former Yugoslav Republic of'),
(450, 'MG', 'MDG', 'Madagascar'),
(454, 'MW', 'MWI', 'Malawi'),
(458, 'MY', 'MYS', 'Malaysia'),
(462, 'MV', 'MDV', 'Maldives'),
(466, 'ML', 'MLI', 'Mali'),
(470, 'MT', 'MLT', 'Malta'),
(584, 'MH', 'MHL', 'Marshall Islands'),
(474, 'MQ', 'MTQ', 'Martinique'),
(478, 'MR', 'MRT', 'Mauritania'),
(480, 'MU', 'MUS', 'Mauritius'),
(175, 'YT', 'MYT', 'Mayotte'),
(484, 'MX', 'MEX', 'Mexico'),
(583, 'FM', 'FSM', 'Micronesia, Federated States of'),
(498, 'MD', 'MDA', 'Moldova'),
(492, 'MC', 'MCO', 'Monaco'),
(496, 'MN', 'MNG', 'Mongolia'),
(499, 'ME', 'MNE', 'Montenegro'),
(500, 'MS', 'MSR', 'Montserrat'),
(504, 'MA', 'MAR', 'Morocco'),
(508, 'MZ', 'MOZ', 'Mozambique'),
(104, 'MM', 'MMR', 'Myanmar'),
(516, 'NA', 'NAM', 'Namibia'),
(520, 'NR', 'NRU', 'Nauru'),
(524, 'NP', 'NPL', 'Nepal'),
(528, 'NL', 'NLD', 'Netherlands'),
(530, 'AN', 'ANT', 'Netherlands Antilles'),
(540, 'NC', 'NCL', 'New Caledonia'),
(554, 'NZ', 'NZL', 'New Zealand'),
(558, 'NI', 'NIC', 'Nicaragua'),
(562, 'NE', 'NER', 'Niger'),
(566, 'NG', 'NGA', 'Nigeria'),
(570, 'NU', 'NIU', 'Niue'),
(574, 'NF', 'NFK', 'Norfolk Island'),
(580, 'MP', 'MNP', 'Northern Mariana Islands'),
(578, 'NO', 'NOR', 'Norway'),
(512, 'OM', 'OMN', 'Oman'),
(586, 'PK', 'PAK', 'Pakistan'),
(585, 'PW', 'PLW', 'Palau'),
(275, 'PS', 'PSE', 'Palestinian Territory, Occupied'),
(591, 'PA', 'PAN', 'Panama'),
(598, 'PG', 'PNG', 'Papua New Guinea'),
(600, 'PY', 'PRY', 'Paraguay'),
(604, 'PE', 'PER', 'Peru'),
(608, 'PH', 'PHL', 'Philippines'),
(612, 'PN', 'PCN', 'Pitcairn'),
(616, 'PL', 'POL', 'Poland'),
(620, 'PT', 'PRT', 'Portugal'),
(630, 'PR', 'PRI', 'Puerto Rico'),
(634, 'QA', 'QAT', 'Qatar'),
(638, 'RE', 'REU', 'Réunion'),
(642, 'RO', 'ROU', 'Romania'),
(643, 'RU', 'RUS', 'Russian Federation'),
(646, 'RW', 'RWA', 'Rwanda'),
(652, 'BL', 'BLM', 'Saint Barthélemy'),
(654, 'SH', 'SHN', 'Saint Helena'),
(659, 'KN', 'KNA', 'Saint Kitts and Nevis'),
(662, 'LC', 'LCA', 'Saint Lucia'),
(663, 'MF', 'MAF', 'Saint Martin (French part)'),
(666, 'PM', 'SPM', 'Saint Pierre and Miquelon'),
(670, 'VC', 'VCT', 'Saint Vincent and the Grenadines'),
(882, 'WS', 'WSM', 'Samoa'),
(674, 'SM', 'SMR', 'San Marino'),
(678, 'ST', 'STP', 'Sao Tome and Principe'),
(682, 'SA', 'SAU', 'Saudi Arabia'),
(686, 'SN', 'SEN', 'Senegal'),
(688, 'RS', 'SRB', 'Serbia[5]'),
(690, 'SC', 'SYC', 'Seychelles'),
(694, 'SL', 'SLE', 'Sierra Leone'),
(702, 'SG', 'SGP', 'Singapore'),
(703, 'SK', 'SVK', 'Slovakia'),
(705, 'SI', 'SVN', 'Slovenia'),
(90, 'SB', 'SLB', 'Solomon Islands'),
(706, 'SO', 'SOM', 'Somalia'),
(710, 'ZA', 'ZAF', 'South Africa'),
(239, 'GS', 'SGS', 'South Georgia and the South Sandwich Islands'),
(724, 'ES', 'ESP', 'Spain'),
(144, 'LK', 'LKA', 'Sri Lanka'),
(736, 'SD', 'SDN', 'Sudan'),
(740, 'SR', 'SUR', 'Suriname'),
(744, 'SJ', 'SJM', 'Svalbard and Jan Mayen'),
(748, 'SZ', 'SWZ', 'Swaziland'),
(752, 'SE', 'SWE', 'Sweden'),
(756, 'CH', 'CHE', 'Switzerland'),
(760, 'SY', 'SYR', 'Syrian Arab Republic'),
(158, 'TW', 'TWN', 'Taiwan, Province of China'),
(762, 'TJ', 'TJK', 'Tajikistan'),
(834, 'TZ', 'TZA', 'Tanzania, United Republic of'),
(764, 'TH', 'THA', 'Thailand'),
(626, 'TL', 'TLS', 'Timor-Leste'),
(768, 'TG', 'TGO', 'Togo'),
(772, 'TK', 'TKL', 'Tokelau'),
(776, 'TO', 'TON', 'Tonga'),
(780, 'TT', 'TTO', 'Trinidad and Tobago'),
(788, 'TN', 'TUN', 'Tunisia'),
(792, 'TR', 'TUR', 'Turkey'),
(795, 'TM', 'TKM', 'Turkmenistan'),
(796, 'TC', 'TCA', 'Turks and Caicos Islands'),
(798, 'TV', 'TUV', 'Tuvalu'),
(800, 'UG', 'UGA', 'Uganda'),
(804, 'UA', 'UKR', 'Ukraine'),
(784, 'AE', 'ARE', 'United Arab Emirates'),
(826, 'GB', 'GBR', 'United Kingdom'),
(840, 'US', 'USA', 'United States'),
(581, 'UM', 'UMI', 'United States Minor Outlying Islands'),
(858, 'UY', 'URY', 'Uruguay'),
(860, 'UZ', 'UZB', 'Uzbekistan'),
(548, 'VU', 'VUT', 'Vanuatu'),
(862, 'VE', 'VEN', 'Venezuela'),
(704, 'VN', 'VNM', 'Viet Nam'),
(92, 'VG', 'VGB', 'Virgin Islands, British'),
(850, 'VI', 'VIR', 'Virgin Islands, U.S.'),
(876, 'WF', 'WLF', 'Wallis and Futuna'),
(732, 'EH', 'ESH', 'Western Sahara'),
(887, 'YE', 'YEM', 'Yemen'),
(894, 'ZM', 'ZMB', 'Zambia'),
(716, 'ZW', 'ZWE', 'Zimbabwe');

-- --------------------------------------------------------

-- 
-- Table structure for table `custom_fields`
-- 

CREATE TABLE `custom_fields` (
  `custom_field_id` int(11) NOT NULL auto_increment,
  `custom_field_group` int(11) NOT NULL,
  `custom_field_name` varchar(50) NOT NULL,
  `custom_field_friendly_name` varchar(255) NOT NULL,
  `custom_field_order` int(11) NOT NULL,
  `custom_field_type` varchar(50) NOT NULL,
  `custom_field_options` text,
  `custom_field_width` varchar(25),
  `custom_field_default` varchar(200),
  `custom_field_required` tinyint(1) NOT NULL,
  `custom_field_validators` text,
  `custom_field_help_text` text,
  PRIMARY KEY  (`custom_field_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `custom_field_groups`
-- 

CREATE TABLE `custom_field_groups` (
  `custom_field_group_id` int(11) NOT NULL auto_increment,
  `custom_field_group_name` varchar(150) NOT NULL,
   PRIMARY KEY  (`custom_field_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ;

INSERT INTO `custom_field_groups` (`custom_field_group_id`, `custom_field_group_name`) VALUES ('1', 'Members');

-- --------------------------------------------------------

-- 
-- Table structure for table `email_triggers`
-- 

CREATE TABLE `email_triggers` (
  `email_trigger_id` int(11) NOT NULL auto_increment,
  `system_name` varchar(50) NOT NULL,
  `human_name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `available_variables` text NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY  (`email_trigger_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- 
-- Dumping data for table `email_triggers`
-- 

INSERT INTO `email_triggers` (`email_trigger_id`, `system_name`, `human_name`, `description`, `available_variables`, `active`) VALUES
(1, 'subscription_charge', 'Subscription Payment', 'Subsequent recurring charges (all but the first charge).', 'a:28:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:9:"charge_id";i:3;s:14:"card_last_four";i:4;s:15:"subscription_id";i:5;s:23:"subscription_start_date";i:6;s:21:"subscription_end_date";i:7;s:24:"subscription_expiry_date";i:8;s:29:"subscription_next_charge_date";i:9;s:19:"subscription_amount";i:10;s:7:"plan_id";i:11;s:9:"plan_name";i:12;s:18:"billing_first_name";i:13;s:17:"billing_last_name";i:14;s:15:"billing_company";i:15;s:17:"billing_address_1";i:16;s:17:"billing_address_2";i:17;s:12:"billing_city";i:18;s:13:"billing_state";i:19;s:19:"billing_postal_code";i:20;s:15:"billing_country";i:21;s:9:"member_id";i:22;s:17:"member_first_name";i:23;s:16:"member_last_name";i:24;s:12:"member_email";i:25;s:15:"member_username";i:26;s:12:"account_link";i:27;s:9:"site_link";}', 1),
(2, 'subscription_expire', 'Subscription Expiration', 'Subscription ends gracefully at expiration date with max_occurrences/end_date limitation', 'a:14:{i:0;s:15:"subscription_id";i:1;s:23:"subscription_start_date";i:2;s:21:"subscription_end_date";i:3;s:24:"subscription_expiry_date";i:4;s:19:"subscription_amount";i:5;s:7:"plan_id";i:6;s:9:"plan_name";i:7;s:9:"member_id";i:8;s:17:"member_first_name";i:9;s:16:"member_last_name";i:10;s:12:"member_email";i:11;s:15:"member_username";i:12;s:12:"account_link";i:13;s:9:"site_link";}', 1),
(3, 'subscription_cancel', 'Subscription Cancellation', 'Subscription ends with an explicit CancelRecurring call.  Not a graceful expiration.', 'a:14:{i:0;s:15:"subscription_id";i:1;s:23:"subscription_start_date";i:2;s:21:"subscription_end_date";i:3;s:24:"subscription_expiry_date";i:4;s:19:"subscription_amount";i:5;s:7:"plan_id";i:6;s:9:"plan_name";i:7;s:9:"member_id";i:8;s:17:"member_first_name";i:9;s:16:"member_last_name";i:10;s:12:"member_email";i:11;s:15:"member_username";i:12;s:12:"account_link";i:13;s:9:"site_link";}', 1),
(4, 'subscription_expiring_in_week', 'Subscription to Expire in a Week', 'Subscription will expire in one week.', 'a:24:{i:0;s:15:"subscription_id";i:1;s:23:"subscription_start_date";i:2;s:21:"subscription_end_date";i:3;s:24:"subscription_expiry_date";i:4;s:29:"subscription_next_charge_date";i:5;s:19:"subscription_amount";i:6;s:7:"plan_id";i:7;s:9:"plan_name";i:8;s:18:"billing_first_name";i:9;s:17:"billing_last_name";i:10;s:15:"billing_company";i:11;s:17:"billing_address_1";i:12;s:17:"billing_address_2";i:13;s:12:"billing_city";i:14;s:13:"billing_state";i:15;s:19:"billing_postal_code";i:16;s:15:"billing_country";i:17;s:9:"member_id";i:18;s:17:"member_first_name";i:19;s:16:"member_last_name";i:20;s:12:"member_email";i:21;s:15:"member_username";i:22;s:12:"account_link";i:23;s:9:"site_link";}', 1),
(5, 'subscription_expiring_in_month', 'Subscription to Expire in a Month', 'Subscription will expire in one month.', 'a:24:{i:0;s:15:"subscription_id";i:1;s:23:"subscription_start_date";i:2;s:21:"subscription_end_date";i:3;s:24:"subscription_expiry_date";i:4;s:29:"subscription_next_charge_date";i:5;s:19:"subscription_amount";i:6;s:7:"plan_id";i:7;s:9:"plan_name";i:8;s:18:"billing_first_name";i:9;s:17:"billing_last_name";i:10;s:15:"billing_company";i:11;s:17:"billing_address_1";i:12;s:17:"billing_address_2";i:13;s:12:"billing_city";i:14;s:13:"billing_state";i:15;s:19:"billing_postal_code";i:16;s:15:"billing_country";i:17;s:9:"member_id";i:18;s:17:"member_first_name";i:19;s:16:"member_last_name";i:20;s:12:"member_email";i:21;s:15:"member_username";i:22;s:12:"account_link";i:23;s:9:"site_link";}', 1),
(6, 'subscription_autorecur_in_week', 'Subscription to Autocharge in a Week', 'Subscription will Autocharge in one week.', 'a:24:{i:0;s:15:"subscription_id";i:1;s:23:"subscription_start_date";i:2;s:21:"subscription_end_date";i:3;s:24:"subscription_expiry_date";i:4;s:29:"subscription_next_charge_date";i:5;s:19:"subscription_amount";i:6;s:7:"plan_id";i:7;s:9:"plan_name";i:8;s:18:"billing_first_name";i:9;s:17:"billing_last_name";i:10;s:15:"billing_company";i:11;s:17:"billing_address_1";i:12;s:17:"billing_address_2";i:13;s:12:"billing_city";i:14;s:13:"billing_state";i:15;s:19:"billing_postal_code";i:16;s:15:"billing_country";i:17;s:9:"member_id";i:18;s:17:"member_first_name";i:19;s:16:"member_last_name";i:20;s:12:"member_email";i:21;s:15:"member_username";i:22;s:12:"account_link";i:23;s:9:"site_link";}', 1),
(7, 'subscription_autorecur_in_month', 'Subscription to Autocharge in a Month', 'Subscription will Autocharge in one month.', 'a:24:{i:0;s:15:"subscription_id";i:1;s:23:"subscription_start_date";i:2;s:21:"subscription_end_date";i:3;s:24:"subscription_expiry_date";i:4;s:29:"subscription_next_charge_date";i:5;s:19:"subscription_amount";i:6;s:7:"plan_id";i:7;s:9:"plan_name";i:8;s:18:"billing_first_name";i:9;s:17:"billing_last_name";i:10;s:15:"billing_company";i:11;s:17:"billing_address_1";i:12;s:17:"billing_address_2";i:13;s:12:"billing_city";i:14;s:13:"billing_state";i:15;s:19:"billing_postal_code";i:16;s:15:"billing_country";i:17;s:9:"member_id";i:18;s:17:"member_first_name";i:19;s:16:"member_last_name";i:20;s:12:"member_email";i:21;s:15:"member_username";i:22;s:12:"account_link";i:23;s:9:"site_link";}', 1),
(8, 'new_subscription', 'New Subscription', 'A new subscription is started.', 'a:28:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:9:"charge_id";i:3;s:14:"card_last_four";i:4;s:15:"subscription_id";i:5;s:23:"subscription_start_date";i:6;s:21:"subscription_end_date";i:7;s:24:"subscription_expiry_date";i:8;s:29:"subscription_next_charge_date";i:9;s:19:"subscription_amount";i:10;s:7:"plan_id";i:11;s:9:"plan_name";i:12;s:18:"billing_first_name";i:13;s:17:"billing_last_name";i:14;s:15:"billing_company";i:15;s:17:"billing_address_1";i:16;s:17:"billing_address_2";i:17;s:12:"billing_city";i:18;s:13:"billing_state";i:19;s:19:"billing_postal_code";i:20;s:15:"billing_country";i:21;s:9:"member_id";i:22;s:17:"member_first_name";i:23;s:16:"member_last_name";i:24;s:12:"member_email";i:25;s:15:"member_username";i:26;s:12:"account_link";i:27;s:9:"site_link";}', 1),
(9, 'new_store_order', 'New Store Order', 'A customer purchases one or more products from the storefront.', 'a:29:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:9:"charge_id";i:3;s:14:"card_last_four";i:4;s:18:"billing_first_name";i:5;s:17:"billing_last_name";i:6;s:15:"billing_company";i:7;s:17:"billing_address_1";i:8;s:17:"billing_address_2";i:9;s:12:"billing_city";i:10;s:13:"billing_state";i:11;s:19:"billing_postal_code";i:12;s:15:"billing_country";i:13;s:19:"shipping_first_name";i:14;s:18:"shipping_last_name";i:15;s:16:"shipping_company";i:16;s:18:"shipping_address_1";i:17;s:18:"shipping_address_2";i:18;s:13:"shipping_city";i:19;s:14:"shipping_state";i:20;s:20:"shipping_postal_code";i:21;s:16:"shipping_country";i:22;s:9:"member_id";i:23;s:17:"member_first_name";i:24;s:16:"member_last_name";i:25;s:12:"member_email";i:26;s:15:"member_username";i:27;s:12:"account_link";i:28;s:9:"site_link";}', 1),
(10, 'downloadable_product', 'Downloadable Product Purchase', 'A customer purchases a downloadable product, delivered via email.', 'a:18:{i:0;s:9:"member_id";i:1;s:17:"member_first_name";i:2;s:16:"member_last_name";i:3;s:12:"member_email";i:4;s:15:"member_username";i:5;s:13:"download_link";i:6;s:12:"product_name";i:7;s:19:"shipping_first_name";i:8;s:18:"shipping_last_name";i:9;s:16:"shipping_company";i:10;s:18:"shipping_address_1";i:11;s:18:"shipping_address_2";i:12;s:13:"shipping_city";i:13;s:14:"shipping_state";i:14;s:20:"shipping_postal_code";i:15;s:16:"shipping_country";i:16;s:12:"account_link";i:17;s:9:"site_link";}', 1),
(11, 'new_member', 'Member Registration', 'A new user registers for a member account.', 'a:8:{i:0;s:9:"member_id";i:1;s:17:"member_first_name";i:2;s:16:"member_last_name";i:3;s:12:"member_email";i:4;s:15:"member_username";i:5;s:8:"password";i:6;s:12:"account_link";i:7;s:9:"site_link";}', 1),
(12, 'forgot_password', 'Forgot Password', 'A user requests a new password because they forgot theirs.', 'a:8:{i:0;s:9:"member_id";i:1;s:17:"member_first_name";i:2;s:16:"member_last_name";i:3;s:12:"member_email";i:4;s:15:"member_username";i:5;s:12:"new_password";i:6;s:12:"account_link";i:7;s:9:"site_link";}', 1),
(13, 'validate_email', 'Validate Email', 'During registration, a user must validate their email prior to continuing.', 'a:9:{i:0;s:9:"member_id";i:1;s:17:"member_first_name";i:2;s:16:"member_last_name";i:3;s:12:"member_email";i:4;s:15:"member_username";i:5;s:12:"account_link";i:6;s:9:"site_link";i:7;s:15:"validation_link";i:8;s:15:"validation_code";}', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `emails`
-- 

CREATE TABLE `emails` (
  `email_id` int(11) NOT NULL auto_increment,
  `trigger_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `to_address` varchar(255) NOT NULL,
  `bcc_address` varchar(255) default NULL,
  `email_subject` varchar(255) NOT NULL,
  `email_body` text NOT NULL,
  `from_name` varchar(50) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `is_html` tinyint(1) NOT NULL,
  `bcc_client` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY  (`email_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `emails`
-- 

INSERT INTO `emails` (`email_id`, `trigger_id`, `plan_id`, `to_address`, `bcc_address`, `email_subject`, `email_body`, `from_name`, `from_email`, `is_html`, `bcc_client`, `active`) VALUES (1, 1, 0, 'user', 'site_email', 'Thank you for your subscription payment to [[SITE_NAME]]', 'Hello [[MEMBER_FIRST_NAME]],\r\n\r\nThank you for your subscription payment!  You were charged $[[AMOUNT]] on [[DATE]].\r\n\r\nYou will be charged again on [[SUBSCRIPTION_NEXT_CHARGE_DATE]].\r\n\r\nTo review your complete billing history or manage your subscriptions, visit [[SITE_LINK]].', '', '', 0, 0, 1),
(2, 2, 0, 'user', 'site_email', 'Your subscription has expired at [[SITE_NAME]]', 'Hello [[MEMBER_FIRST_NAME]],\r\n\r\nYour subscription to [[PLAN_NAME]] at [[SITE_NAME]] has expired.  There will be no future charges for this subscription.\r\n\r\nTo review your complete billing history or add a new subscription to your account, visit [[SITE_LINK]].', '', '', 0, 0, 1),
(3, 3, 0, 'user', 'site_email', 'You have cancelled your subscription at [[SITE_NAME]]', 'Hello [[MEMBER_FIRST_NAME]],\r\n\r\nYour subscription to [[PLAN_NAME]] has been cancelled successfully.  You will not be charged again.\r\n\r\nTo review your complete billing history or manage your subscriptions, visit [[SITE_LINK]].', '', '', 0, 0, 1),
(4, 8, 0, 'user', 'site_email', 'You are now subscribed to [[PLAN_NAME]]', 'Hi [[MEMBER_FIRST_NAME]],\r\n\r\nThank you for subscribing to [[PLAN_NAME]]!  We really appreciate it.\r\n\r\nYou have been charged $[[AMOUNT]] today and will be charged $[[SUBSCRIPTION_AMOUNT]] again on [[SUBSCRIPTION_NEXT_CHARGE_DATE]].\r\n\r\nTo login to your account and manage your profile and subscriptions, visit [[ACCOUNT_LINK]].', '', '', 0, 0, 1),
(5, 9, 0, 'user', 'site_email', 'Thank you for your order from [[SITE_NAME]]!', 'Hello [[MEMBER_FIRST_NAME]],\r\n\r\nThank you for your order from [[SITE_NAME]]!  You were charged $[[AMOUNT]] on [[DATE]].\r\n\r\nIf your product is a downloadable product, you will receive an email very shortly with a download link.\r\n\r\nIf your product is being shipped, we have your shipping address and the package will be sent soon.\r\n\r\nTo manage your profile or see your billing history, please visit [[ACCOUNT_LINK]].\r\n\r\nThanks again!', '', '', 0, 0, 1),
(6, 10, 0, 'user', '', 'Download your purchase from [[SITE_NAME]]', 'Hello [[MEMBER_FIRST_NAME]],\r\n\r\nThank you for purchasing from [[SITE_NAME]].\r\n\r\nYou may now download your purchase, [[PRODUCT_NAME]], from:\r\n\r\n[[DOWNLOAD_LINK]]\r\n\r\nTo manage your profile or see your billing history, please visit [[ACCOUNT_LINK]].\r\n\r\nThanks again!', '', '', 0, 0, 1),
(7, 11, 0, 'user', 'site_email', 'Your account registration at [[SITE_NAME]]', 'Hi [[MEMBER_FIRST_NAME]],\r\n\r\nThank you for registering for an account at [[SITE_NAME]]!\r\n\r\nYour login information is below:\r\n\r\nUsername: [[MEMBER_USERNAME]]\r\nEmail: [[MEMBER_EMAIL]]\r\nPassword: [[PASSWORD]]\r\n\r\nYou may login now at [[SITE_LINK]].', '', '', 0, 0, 1),
(8, 12, 0, 'user', '', 'Your password has been reset at [[SITE_NAME]]', 'Hi [[MEMBER_FIRST_NAME]],\r\n\r\nYour password has been reset at [[SITE_NAME]].\r\n\r\nYour new login information is below:\r\n\r\nUsername: [[MEMBER_USERNAME]]\r\nEmail: [[MEMBER_EMAIL]]\r\nPassword: [[NEW_PASSWORD]]\r\n\r\nYou may login now at [[SITE_LINK]].', '', '', 0, 0, 1);

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `links` (
	 `link_id` int(11) NOT NULL auto_increment,
	 `link_topics` varchar(255),
	 `link_url_path` varchar(255) NOT NULL,
	 `link_title` varchar(255) NOT NULL,
	 `link_type` varchar(255) NOT NULL,
	 `link_module` varchar(250),
	 `link_controller` varchar(250),
	 `link_method` varchar(250),
	 PRIMARY KEY  (`link_id`)
 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Table structure for table `modules`
-- 

CREATE TABLE `modules` (
  `module_id` int(11) NOT NULL auto_increment,
  `module_name` varchar(50) NOT NULL ,
  `module_version` varchar(25) NOT NULL ,
  PRIMARY KEY  (`module_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `settings`
-- 

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL auto_increment,
  `setting_group` int(11) default NULL,
  `setting_name` varchar(250) default NULL,
  `setting_value` text default NULL,
  `setting_help` varchar(250) default NULL,
  `setting_update_date` datetime default NULL,
  `setting_type` varchar(250) default NULL,
  `setting_options` text,
  `setting_hidden` tinyint(1),
  PRIMARY KEY  (`setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- 
-- Dumping data for table `settings`
-- 

INSERT INTO `settings` (`setting_group`, `setting_name`, `setting_value`, `setting_help`, `setting_update_date`, `setting_type`, `setting_options`, `setting_hidden`) VALUES
(1, 'site_name', 'Your Website', 'The name of your website.', NOW(), 'text', '', '0'),
(1, 'site_email', 'email@example.com', 'The reply-to email address for all outgoing system emails.', NOW(), 'text', '', '0'),
(1, 'email_name', 'Your Website', 'The reply-to name for all outgoing system emails.', NOW(), 'text', '', '0'),
(2, 'currency_symbol', '$', 'Denotes currency on the site and in emails.', NOW(), 'text', '', '0'),
(1, 'ssl_certificate', '0', 'If you have an SSL certificate for your domain installed, this setting will force sensitive information to be transferred via HTTPS.', NOW(), 'toggle', 'a:2:{i:0;s:3:"Off";i:1;s:2:"On";}', '0'),
(2, 'default_gateway', '0', 'This payment gateway, referenced by ID, will be the default gateway for site purchases.', NOW(), 'text', '', '0'),
(1, 'locale', 'US', 'Some payment gateways and other integrations require this 2-character ISO-standard country code to determine your locale.', NOW(), 'text', '', '0'),
(1, 'email_signature', 'Sincerely,\nThe [[SITE_NAME]] Team\n[[SITE_LINK]]', 'If this setting is set, it will be attached to each outgoing email', NOW(), 'textarea', '', '0'),
(1, 'use_time_since', '1', 'Should we display dates within 24 hours as "X minutes/hours ago"?', NOW(), 'toggle', 'a:2:{i:0;s:3:"Off";i:1;s:2:"On";}', '0'),
(1, 'date_format', 'd-M-Y h:ia', 'The default format (in PHP date() style) for system dates.', NOW(), 'text', '', '0');

-- --------------------------------------------------------

-- 
-- Table structure for table `settings_groups`
-- 

CREATE TABLE `settings_groups` (
  `setting_group_id` int(11) NOT NULL auto_increment,
  `setting_group_name` varchar(250) default NULL,
  `setting_group_help` varchar(250) default NULL,
  PRIMARY KEY  (`setting_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `settings_groups`
-- 

INSERT INTO `settings_groups` (`setting_group_id`, `setting_group_name`, `setting_group_help`) VALUES (1, 'Core', 'Core system settings.'),
(2, 'E-commerce', 'Configurations related to subscriptions, products, and checkout.'),
(3, 'Members', 'Related to registration and site members.'),
(4, 'Publishing', 'Related to site content and publishing.'),
(5, 'Design', 'Configures the look of your site.  Often managed in the Design tab.'),
(6, 'Search', 'How should the site searching work?');

-- --------------------------------------------------------

-- 
-- Table structure for table `states`
-- 

CREATE TABLE `states` (
  `state_id` int(11) NOT NULL auto_increment,
  `name_long` varchar(20) NOT NULL default '' COMMENT 'Common Name',
  `name_short` char(2) NOT NULL default '' COMMENT 'USPS Abbreviation',
  PRIMARY KEY  (`state_id`),
  UNIQUE KEY `name_long` (`name_long`)
) ENGINE=MyISAM AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COMMENT='US States' AUTO_INCREMENT=64 ;

-- 
-- Dumping data for table `states`
-- 

INSERT INTO `states` (`state_id`, `name_long`, `name_short`) VALUES (1, 'Alabama', 'AL'),
(2, 'Alaska', 'AK'),
(3, 'Arizona', 'AZ'),
(4, 'Arkansas', 'AR'),
(5, 'California', 'CA'),
(6, 'Colorado', 'CO'),
(7, 'Connecticut', 'CT'),
(8, 'Delaware', 'DE'),
(9, 'Florida', 'FL'),
(10, 'Georgia', 'GA'),
(11, 'Hawaii', 'HI'),
(12, 'Idaho', 'ID'),
(13, 'Illinois', 'IL'),
(14, 'Indiana', 'IN'),
(15, 'Iowa', 'IA'),
(16, 'Kansas', 'KS'),
(17, 'Kentucky', 'KY'),
(18, 'Louisiana', 'LA'),
(19, 'Maine', 'ME'),
(20, 'Maryland', 'MD'),
(21, 'Massachusetts', 'MA'),
(22, 'Michigan', 'MI'),
(23, 'Minnesota', 'MN'),
(24, 'Mississippi', 'MS'),
(25, 'Missouri', 'MO'),
(26, 'Montana', 'MT'),
(27, 'Nebraska', 'NE'),
(28, 'Nevada', 'NV'),
(29, 'New Hampshire', 'NH'),
(30, 'New Jersey', 'NJ'),
(31, 'New Mexico', 'NM'),
(32, 'New York', 'NY'),
(33, 'North Carolina', 'NC'),
(34, 'North Dakota', 'ND'),
(35, 'Ohio', 'OH'),
(36, 'Oklahoma', 'OK'),
(37, 'Oregon', 'OR'),
(38, 'Pennsylvania', 'PA'),
(39, 'Rhode Island', 'RI'),
(40, 'South Carolina', 'SC'),
(41, 'South Dakota', 'SD'),
(42, 'Tennessee', 'TN'),
(43, 'Texas', 'TX'),
(44, 'Utah', 'UT'),
(45, 'Vermont', 'VT'),
(46, 'Virginia', 'VA'),
(47, 'Washington', 'WA'),
(48, 'West Virginia', 'WV'),
(49, 'Wisconsin', 'WI'),
(50, 'Wyoming', 'WY'),
(51, 'Alberta', 'AB'),
(52, 'British Columbia', 'BC'),
(53, 'Manitoba', 'MB'),
(54, 'New Brunswick', 'NB'),
(55, 'Newfoundland and Lab', 'NL'),
(56, 'Northwest Territorie', 'NT'),
(57, 'Nova Scotia', 'NS'),
(58, 'Nunavut', 'NU'),
(59, 'Ontario', 'ON'),
(60, 'Prince Edward Island', 'PE'),
(61, 'Quebec', 'QC'),
(62, 'Saskatchewan', 'SK'),
(63, 'Yukon', 'YT');

-- --------------------------------------------------------

-- 
-- Table structure for table `system`
-- 

CREATE TABLE `system` (
  `db_version` varchar(15) NOT NULL,
  PRIMARY KEY  (`db_version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `system`
-- 

INSERT INTO `system` (`db_version`) VALUES ('3.0');
