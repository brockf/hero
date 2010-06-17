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
(1, 'subscription_charge', 'Subscription Payment', 'Subsequent recurring charges (all but the first charge).', 'a:21:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:12:"recurring_id";i:3;s:9:"charge_id";i:4;s:14:"card_last_four";i:5;s:16:"next_charge_date";i:6;s:7:"plan_id";i:7;s:9:"plan_name";i:8;s:11:"customer_id";i:9;s:20:"customer_internal_id";i:10;s:19:"customer_first_name";i:11;s:18:"customer_last_name";i:12;s:16:"customer_company";i:13;s:18:"customer_address_1";i:14;s:18:"customer_address_2";i:15;s:13:"customer_city";i:16;s:14:"customer_state";i:17;s:20:"customer_postal_code";i:18;s:16:"customer_country";i:19;s:14:"customer_phone";i:20;s:14:"customer_email";}', 1),
(2, 'subscription_expire', 'Subscription Expiration', 'Subscription ends gracefully at expiration date with max_occurrences/end_date limitation', 'a:23:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:12:"recurring_id";i:3;s:9:"charge_id";i:4;s:14:"card_last_four";i:5;s:10:"start_date";i:6;s:8:"end_date";i:7;s:16:"next_charge_date";i:8;s:7:"plan_id";i:9;s:9:"plan_name";i:10;s:11:"customer_id";i:11;s:20:"customer_internal_id";i:12;s:19:"customer_first_name";i:13;s:18:"customer_last_name";i:14;s:16:"customer_company";i:15;s:18:"customer_address_1";i:16;s:18:"customer_address_2";i:17;s:13:"customer_city";i:18;s:14:"customer_state";i:19;s:20:"customer_postal_code";i:20;s:16:"customer_country";i:21;s:14:"customer_phone";i:22;s:14:"customer_email";}', 1),
(3, 'subscription_cancel', 'Subscription Cancellation', 'Subscription ends with an explicit CancelRecurring call.  Not a graceful expiration.', 'a:18:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:12:"recurring_id";i:3;s:7:"plan_id";i:4;s:9:"plan_name";i:5;s:11:"customer_id";i:6;s:20:"customer_internal_id";i:7;s:19:"customer_first_name";i:8;s:18:"customer_last_name";i:9;s:16:"customer_company";i:10;s:18:"customer_address_1";i:11;s:18:"customer_address_2";i:12;s:13:"customer_city";i:13;s:14:"customer_state";i:14;s:20:"customer_postal_code";i:15;s:16:"customer_country";i:16;s:14:"customer_phone";i:17;s:14:"customer_email";}', 1),
(4, 'subscription_expiring_in_week', 'Subscription to Expire in a Week', 'Subscription will expire in one week.', 'a:19:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:12:"recurring_id";i:3;s:11:"expiry_date";i:4;s:7:"plan_id";i:5;s:9:"plan_name";i:6;s:11:"customer_id";i:7;s:20:"customer_internal_id";i:8;s:19:"customer_first_name";i:9;s:18:"customer_last_name";i:10;s:16:"customer_company";i:11;s:18:"customer_address_1";i:12;s:18:"customer_address_2";i:13;s:13:"customer_city";i:14;s:14:"customer_state";i:15;s:20:"customer_postal_code";i:16;s:16:"customer_country";i:17;s:14:"customer_phone";i:18;s:14:"customer_email";}', 1),
(5, 'subscription_expiring_in_month', 'Subscription to Expire in a Month', 'Subscription will expire in one month.', 'a:19:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:12:"recurring_id";i:3;s:11:"expiry_date";i:4;s:7:"plan_id";i:5;s:9:"plan_name";i:6;s:11:"customer_id";i:7;s:20:"customer_internal_id";i:8;s:19:"customer_first_name";i:9;s:18:"customer_last_name";i:10;s:16:"customer_company";i:11;s:18:"customer_address_1";i:12;s:18:"customer_address_2";i:13;s:13:"customer_city";i:14;s:14:"customer_state";i:15;s:20:"customer_postal_code";i:16;s:16:"customer_country";i:17;s:14:"customer_phone";i:18;s:14:"customer_email";}', 1),
(6, 'subscription_autorecur_in_week', 'Subscription to Autocharge in a Week', 'Subscription will Autocharge in one week.', 'a:19:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:12:"recurring_id";i:3;s:16:"next_charge_date";i:4;s:7:"plan_id";i:5;s:9:"plan_name";i:6;s:11:"customer_id";i:7;s:20:"customer_internal_id";i:8;s:19:"customer_first_name";i:9;s:18:"customer_last_name";i:10;s:16:"customer_company";i:11;s:18:"customer_address_1";i:12;s:18:"customer_address_2";i:13;s:13:"customer_city";i:14;s:14:"customer_state";i:15;s:20:"customer_postal_code";i:16;s:16:"customer_country";i:17;s:14:"customer_phone";i:18;s:14:"customer_email";}', 1),
(7, 'subscription_autorecur_in_month', 'Subscription to Autocharge in a Month', 'Subscription will Autocharge in one month.', 'a:19:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:12:"recurring_id";i:3;s:16:"next_charge_date";i:4;s:7:"plan_id";i:5;s:9:"plan_name";i:6;s:11:"customer_id";i:7;s:20:"customer_internal_id";i:8;s:19:"customer_first_name";i:9;s:18:"customer_last_name";i:10;s:16:"customer_company";i:11;s:18:"customer_address_1";i:12;s:18:"customer_address_2";i:13;s:13:"customer_city";i:14;s:14:"customer_state";i:15;s:20:"customer_postal_code";i:16;s:16:"customer_country";i:17;s:14:"customer_phone";i:18;s:14:"customer_email";}', 1),
(8, 'new_subscription', 'New Subscription', 'A new subscription is started.', 'a:21:{i:0;s:6:"amount";i:1;s:4:"date";i:2;s:12:"recurring_id";i:3;s:9:"charge_id";i:4;s:14:"card_last_four";i:5;s:16:"next_charge_date";i:6;s:7:"plan_id";i:7;s:9:"plan_name";i:8;s:11:"customer_id";i:9;s:20:"customer_internal_id";i:10;s:19:"customer_first_name";i:11;s:18:"customer_last_name";i:12;s:16:"customer_company";i:13;s:18:"customer_address_1";i:14;s:18:"customer_address_2";i:15;s:13:"customer_city";i:16;s:14:"customer_state";i:17;s:20:"customer_postal_code";i:18;s:16:"customer_country";i:19;s:14:"customer_phone";i:20;s:14:"customer_email";}', 1);

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

-- --------------------------------------------------------

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
  `setting_value` varchar(250) default NULL,
  `setting_help` varchar(250) default NULL,
  `setting_update_date` datetime default NULL,
  `setting_type` varchar(250) default NULL,
  `setting_options` text,
  PRIMARY KEY  (`setting_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `settings`
-- 

INSERT INTO `settings` (`setting_id`, `setting_group`, `setting_name`, `setting_value`, `setting_help`, `setting_update_date`, `setting_type`, `setting_options`) VALUES
(1, 1, 'site_name', 'Your Website', 'The name of your website.', NOW(), 'text', ''),
(2, 1, 'site_email', 'email@example.com', 'The reply-to email address for all outgoing system emails.', NOW(), 'text', ''),
(3, 1, 'email_name', 'Your Website', 'The reply-to name for all outgoing system emails.', NOW(), 'text', ''),
(4, 2, 'currency_symbol', '$', 'Denotes currency on the site and in emails.', NOW(), 'text', ''),
(5, 1, 'ssl_certificate', '0', 'If you have an SSL certificate for your domain installed, this setting will force sensitive information to be transferred via HTTPS.', NOW(), 'toggle', 'a:2:{i:0;s:2:"Off";i:1;s:3:"On";}');

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
(2, 'E-commerce', 'Ecommece-related configurations.');

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

-- 
-- Dumping data for table `users`
-- 