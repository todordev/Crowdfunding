<?php
/**
 * @package      Crowdfunding
 * @subpackage   Currencies
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Currency;

use Prism\Database;
use Prism\Domain;
use Prism\Money\LegalTender;

/**
 * Currency class of the crowdfunding platform.
 *
 * @package      Crowdfunding
 * @subpackage   Currencies
 *
 * @todo Change this class to be Entity. Implement Domain\Populator.
 */
class Currency extends Database\TableImmutable implements LegalTender, Domain\Entity
{
    use Domain\EntityId;

    const SYMBOL_BEFORE = 0;
    const SYMBOL_AFTER  = 1;

    protected $title;
    protected $code;
    protected $symbol;
    protected $position;

    protected static $instances = array();

    /**
     * Create an object or return existing one.
     *
     * <code>
     * $currencyId = 1;
     *
     * $currency   = Crowdfunding\Currency::getInstance(\JFactory::getDbo(), $currencyId);
     * </code>
     *
     * @param \JDatabaseDriver $db
     * @param int             $id
     * @param array           $options
     *
     * @return null|self
     *
     * @deprecated v2.4 Use the container.
     */
    public static function getInstance(\JDatabaseDriver $db, $id, array $options = array())
    {
        if (!array_key_exists($id, self::$instances)) {
            $item = new Currency($db);
            $item->load($id, $options);

            self::$instances[$id] = $item;
        }

        return self::$instances[$id];
    }

    /**
     * Load currency data from database by ID.
     *
     * <code>
     * $keys = array(
     *     "id" => 1,
     *     "code" => "EUR"
     * );
     *
     * $currency   = new Crowdfunding\Currency(\JFactory::getDbo());
     * $currency->load($keys);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     *
     * @throws \RuntimeException
     * @deprecated v2.6.6 Use Crowdfunding\Currency\Repository.
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('a.id, a.title, a.code, a.symbol, a.position')
            ->from($this->db->quoteName('#__crowdf_currencies', 'a'));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.'.$key) .' = ' . $this->db->quote($value));
            }
        } else {
            $query->where('a.id = ' . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);
    }

    /**
     * Return currency title.
     *
     * <code>
     * $currencyId  = 1;
     *
     * $gateway     = new Crowdfunding\Currency\Gateway\JoomlaGateway(\JFactory::getDbo());
     * $repository  = new Crowdfunding\Currency\Repository($gateway);
     * $currency    = $repository->findById($currencyId);
     *
     * echo $currency->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return currency code (abbreviation).
     *
     * <code>
     * $currencyId  = 1;
     *
     * $gateway     = new Crowdfunding\Currency\Gateway\JoomlaGateway(\JFactory::getDbo());
     * $repository  = new Crowdfunding\Currency\Repository($gateway);
     * $currency    = $repository->findById($currencyId);
     *
     * // Return GBP
     * $code = $currency->getCode();
     * </code>
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Return currency symbol.
     *
     * <code>
     * $currencyId  = 1;
     *
     * $gateway     = new Crowdfunding\Currency\Gateway\JoomlaGateway(\JFactory::getDbo());
     * $repository  = new Crowdfunding\Currency\Repository($gateway);
     * $currency    = $repository->findById($currencyId);
     *
     * // Return £
     * $symbol = $currency->getSymbol();
     * </code>
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Return the position of currency symbol.
     *
     * <code>
     * $currencyId  = 1;
     *
     * $gateway     = new Crowdfunding\Currency\Gateway\JoomlaGateway(\JFactory::getDbo());
     * $repository  = new Crowdfunding\Currency\Repository($gateway);
     * $currency    = $repository->findById($currencyId);
     *
     * // Return 0 = beginning; 1 = end;
     * if (0 == $currency->getPosition()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getPosition()
    {
        return (int)$this->position;
    }

    /**
     * Check if currency symbol should stay at the beginning of the formatted amount string.
     *
     * <code>
     * $data = array(
     *     'title'    => 'EURO',
     *     'code'     => 'EUR',
     *     'symbol'   => '€',
     *     'position' => '0'
     * );
     *
     * $currency  = new Prism\Money\Currency($data);
     * if ($currency->symbolBefore()) {
     * }
     * </code>
     *
     * @return bool
     */
    public function symbolBefore()
    {
        return (self::SYMBOL_BEFORE === $this->getPosition());
    }

    /**
     * Check if currency symbol should stay at the end of the formatted amount string.
     *
     * <code>
     * $data = array(
     *     'title'    => 'EURO',
     *     'code'     => 'EUR',
     *     'symbol'   => '€',
     *     'position' => '0'
     * );
     *
     * $currency  = new Prism\Money\Currency($data);
     * if ($currency->symbolAfter()) {
     * }
     * </code>
     *
     * @return bool
     */
    public function symbolAfter()
    {
        return (self::SYMBOL_AFTER === $this->getPosition());
    }

    /**
     * Checks whether this currency is the same as an other.
     *
     * @param LegalTender $other
     *
     * @return bool
     */
    public function equals(LegalTender $other)
    {
        return $this->code === $other->getCode();
    }
}
