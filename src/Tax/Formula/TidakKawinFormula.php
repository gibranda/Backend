<?php

namespace Persona\Hris\Tax\Formula;

use Persona\Hris\Employee\Model\EmployeeInterface;
use Persona\Hris\Tax\TaxPercentage;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@personahris.com>
 */
class TidakKawinFormula extends AbstractTaxFormula
{
    const TK0 = 54000000;
    const TK1 = 58500000;
    const TK2 = 63000000;
    const TK3 = 67500000;

    /**
     * @param EmployeeInterface $employee
     *
     * @return float
     */
    public function getCalculatedValue(EmployeeInterface $employee): float
    {
        $taxable = $this->getTaxableValue($employee);
        $taxPercentage = TaxPercentage::getPercentageValue($taxable);

        $taxReduce = self::TK0;
        switch ($employee->getTaxGroup()) {
            case EmployeeInterface::TAX_TK_1:
                $taxReduce = self::TK1;
                break;
            case EmployeeInterface::TAX_TK_2:
                $taxReduce = self::TK2;
                break;
            case EmployeeInterface::TAX_TK_3:
                $taxReduce = self::TK3;
                break;
        }

        $taxable = $taxable - $taxReduce;

        return $taxable * $taxPercentage;
    }
}