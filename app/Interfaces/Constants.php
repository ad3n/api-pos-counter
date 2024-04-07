<?php

namespace App\Interfaces;

interface Constants
{
	/**
	 * Merchant type
	 */
	const MERCHANT_TYPE_STREET_FOOD = 'street_food';
	const MERCHANT_TYPE_RESTO = 'resto';
	const MERCHANT_TYPE_GROCERY = 'grocery_store';
	const MERCHANT_TYPE_CAFE = 'cafe';

	/**
	 * Transaction type
	 */
	const TRANSACTION_TYPE_OMZET = 'income';
	const TRANSACTION_TYPE_EXPENSE = 'expense';
	const TRANSACTION_STATUS_SUCCESS = 'success';
	const TRANSACTION_STATUS_PROCESS = 'process';

	/**
	 * Payment method
	 */
	const PAYMENT_METHOD_CASH = 'cash';
	const PAYMENT_METHOD_DEBIT = 'debit_card';
	const PAYMENT_METHOD_CREDIT = 'credit_card';
	const PAYMENT_METHOD_BANK = 'bank_transfer';
	const PAYMENT_METHOD_BONUS = 'bonus';

	/**
	 * Payment status
	 */
	const PAYMENT_STATUS_PAID = 'paid';
	const PAYMENT_STATUS_CREDIT = 'credit';

	/**
	 * Referral Status
	 */
	const REFERRAL_STATUS_PENDING = 'pending';
	const REFERRAL_STATUS_REGISTER = 'registered';
	const REFERRAL_STATUS_WITHDRAW = 'withdrawn';

	/**
	 * Saldo Type
	 */
	const SALDO_TYPE_FREE = 'free';
	const SALDO_TYPE_DEPOSIT = 'deposit';
	const SALDO_TYPE_REFERRAL = 'referral_bonus';
	const SALDO_STATUS_OK = 'ok';

	/**
	 * Saldo Usage
	 */
	const SALDO_USAGE_WARNING = 'warning';
	const SALDO_USAGE_DANGER = 'danger';

	/**
	 * Type of product
	 */
	const PRODUCT_TYPE_PC = 'piece';
	const PRODUCT_TYPE_VOLUME = 'volume';
	const PRODUCT_TYPE_SALDO = 'saldo';

    /**
	 * Type of Qty
	 */
	const STOCK_TYPE_IN = 'in';
	const STOCK_TYPE_OUT = 'out';
}
