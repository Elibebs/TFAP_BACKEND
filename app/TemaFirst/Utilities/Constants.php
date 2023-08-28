<?php

namespace App\TemaFirst\Utilities;

class Constants
{
	const STATUS_ENABLED = 'ENABLED';
	const STATUS_DISABLED = 'DISABLED';

	const STATUS_PENDING = 'PENDING';
    const STATUS_PROCESSING = 'PROCESSING';

	const ORDER_PENDING = 'Pending';
    const ORDER_CHECKEDOUT = 'Checked_out';

	const ADDRESS_STATUS_DEFAULT = 'true';
    const ADDRESS_STATUS_INACTIVE = 'false';



	const ENV_LOCAL = 'local';
	const ENV_TEST = 'test';
	const ENV_PRODUCTION = 'production';

	const FILTER_PARAM_IGNORE_LIST = ['page','pageSize','q','search_text','filter_date'];

	const FILTER_DATE_DAY = 'day';
	const FILTER_DATE_WEEK = 'week';
	const FILTER_DATE_MONTH = 'month';
}
