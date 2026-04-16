<?php

namespace PhpOffice\PhpSpreadsheet\Reader\Xlsx;

<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
=======
>>>>>>> main
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SimpleXMLElement;

class AutoFilter
{
<<<<<<< HEAD
    private Table|Worksheet $parent;

    private SimpleXMLElement $worksheetXml;

    public function __construct(Table|Worksheet $parent, SimpleXMLElement $worksheetXml)
=======
    /**
     * @var Table|Worksheet
     */
    private $parent;

    /**
     * @var SimpleXMLElement
     */
    private $worksheetXml;

    /**
     * @param Table|Worksheet $parent
     */
    public function __construct($parent, SimpleXMLElement $worksheetXml)
>>>>>>> main
    {
        $this->parent = $parent;
        $this->worksheetXml = $worksheetXml;
    }

    public function load(): void
    {
        // Remove all "$" in the auto filter range
<<<<<<< HEAD
        $attrs = $this->worksheetXml->autoFilter->attributes() ?? [];
        $autoFilterRange = (string) preg_replace('/\$/', '', $attrs['ref'] ?? '');
        if (str_contains($autoFilterRange, ':')) {
            $this->readAutoFilter($autoFilterRange);
        }
    }

    private function readAutoFilter(string $autoFilterRange): void
=======
        $autoFilterRange = (string) preg_replace('/\$/', '', $this->worksheetXml->autoFilter['ref'] ?? '');
        if (strpos($autoFilterRange, ':') !== false) {
            $this->readAutoFilter($autoFilterRange, $this->worksheetXml);
        }
    }

    private function readAutoFilter(string $autoFilterRange, SimpleXMLElement $xmlSheet): void
>>>>>>> main
    {
        $autoFilter = $this->parent->getAutoFilter();
        $autoFilter->setRange($autoFilterRange);

<<<<<<< HEAD
        foreach ($this->worksheetXml->autoFilter->filterColumn as $filterColumn) {
            $attributes = $filterColumn->attributes() ?? [];
            $column = $autoFilter->getColumnByOffset((int) ($attributes['colId'] ?? 0));
            //    Check for standard filters
            if ($filterColumn->filters) {
                $column->setFilterType(Column::AUTOFILTER_FILTERTYPE_FILTER);
                $filters = Xlsx::testSimpleXml($filterColumn->filters->attributes());
=======
        foreach ($xmlSheet->autoFilter->filterColumn as $filterColumn) {
            $column = $autoFilter->getColumnByOffset((int) $filterColumn['colId']);
            //    Check for standard filters
            if ($filterColumn->filters) {
                $column->setFilterType(Column::AUTOFILTER_FILTERTYPE_FILTER);
                $filters = $filterColumn->filters;
>>>>>>> main
                if ((isset($filters['blank'])) && ((int) $filters['blank'] == 1)) {
                    //    Operator is undefined, but always treated as EQUAL
                    $column->createRule()->setRule('', '')->setRuleType(Rule::AUTOFILTER_RULETYPE_FILTER);
                }
                //    Standard filters are always an OR join, so no join rule needs to be set
                //    Entries can be either filter elements
<<<<<<< HEAD
                foreach ($filterColumn->filters->filter as $filterRule) {
                    //    Operator is undefined, but always treated as EQUAL
                    /** @var SimpleXMLElement */
                    $attr2 = $filterRule->attributes() ?? ['val' => ''];
                    $column->createRule()->setRule('', (string) $attr2['val'])->setRuleType(Rule::AUTOFILTER_RULETYPE_FILTER);
                }

                //    Or Date Group elements
                $this->readDateRangeAutoFilter($filterColumn->filters, $column);
=======
                foreach ($filters->filter as $filterRule) {
                    //    Operator is undefined, but always treated as EQUAL
                    $column->createRule()->setRule('', (string) $filterRule['val'])->setRuleType(Rule::AUTOFILTER_RULETYPE_FILTER);
                }

                //    Or Date Group elements
                $this->readDateRangeAutoFilter($filters, $column);
>>>>>>> main
            }

            //    Check for custom filters
            $this->readCustomAutoFilter($filterColumn, $column);
            //    Check for dynamic filters
            $this->readDynamicAutoFilter($filterColumn, $column);
            //    Check for dynamic filters
            $this->readTopTenAutoFilter($filterColumn, $column);
        }
        $autoFilter->setEvaluated(true);
    }

    private function readDateRangeAutoFilter(SimpleXMLElement $filters, Column $column): void
    {
<<<<<<< HEAD
        foreach ($filters->dateGroupItem as $dateGroupItemx) {
            //    Operator is undefined, but always treated as EQUAL
            $dateGroupItem = $dateGroupItemx->attributes();
            if ($dateGroupItem !== null) {
                $column->createRule()->setRule(
                    '',
                    [
                        'year' => (string) $dateGroupItem['year'],
                        'month' => (string) $dateGroupItem['month'],
                        'day' => (string) $dateGroupItem['day'],
                        'hour' => (string) $dateGroupItem['hour'],
                        'minute' => (string) $dateGroupItem['minute'],
                        'second' => (string) $dateGroupItem['second'],
                    ],
                    (string) $dateGroupItem['dateTimeGrouping']
                )->setRuleType(Rule::AUTOFILTER_RULETYPE_DATEGROUP);
            }
=======
        foreach ($filters->dateGroupItem as $dateGroupItem) {
            //    Operator is undefined, but always treated as EQUAL
            $column->createRule()->setRule(
                '',
                [
                    'year' => (string) $dateGroupItem['year'],
                    'month' => (string) $dateGroupItem['month'],
                    'day' => (string) $dateGroupItem['day'],
                    'hour' => (string) $dateGroupItem['hour'],
                    'minute' => (string) $dateGroupItem['minute'],
                    'second' => (string) $dateGroupItem['second'],
                ],
                (string) $dateGroupItem['dateTimeGrouping']
            )->setRuleType(Rule::AUTOFILTER_RULETYPE_DATEGROUP);
>>>>>>> main
        }
    }

    private function readCustomAutoFilter(?SimpleXMLElement $filterColumn, Column $column): void
    {
        if (isset($filterColumn, $filterColumn->customFilters)) {
            $column->setFilterType(Column::AUTOFILTER_FILTERTYPE_CUSTOMFILTER);
            $customFilters = $filterColumn->customFilters;
<<<<<<< HEAD
            $attributes = $customFilters->attributes();
            //    Custom filters can an AND or an OR join;
            //        and there should only ever be one or two entries
            if ((isset($attributes['and'])) && ((string) $attributes['and'] === '1')) {
                $column->setJoin(Column::AUTOFILTER_COLUMN_JOIN_AND);
            }
            foreach ($customFilters->customFilter as $filterRule) {
                /** @var SimpleXMLElement */
                $attr2 = $filterRule->attributes() ?? ['operator' => '', 'val' => ''];
                $column->createRule()->setRule(
                    (string) $attr2['operator'],
                    (string) $attr2['val']
=======
            //    Custom filters can an AND or an OR join;
            //        and there should only ever be one or two entries
            if ((isset($customFilters['and'])) && ((string) $customFilters['and'] === '1')) {
                $column->setJoin(Column::AUTOFILTER_COLUMN_JOIN_AND);
            }
            foreach ($customFilters->customFilter as $filterRule) {
                $column->createRule()->setRule(
                    (string) $filterRule['operator'],
                    (string) $filterRule['val']
>>>>>>> main
                )->setRuleType(Rule::AUTOFILTER_RULETYPE_CUSTOMFILTER);
            }
        }
    }

    private function readDynamicAutoFilter(?SimpleXMLElement $filterColumn, Column $column): void
    {
        if (isset($filterColumn, $filterColumn->dynamicFilter)) {
            $column->setFilterType(Column::AUTOFILTER_FILTERTYPE_DYNAMICFILTER);
            //    We should only ever have one dynamic filter
            foreach ($filterColumn->dynamicFilter as $filterRule) {
                //    Operator is undefined, but always treated as EQUAL
<<<<<<< HEAD
                $attr2 = $filterRule->attributes() ?? [];
                $column->createRule()->setRule(
                    '',
                    (string) ($attr2['val'] ?? ''),
                    (string) ($attr2['type'] ?? '')
                )->setRuleType(Rule::AUTOFILTER_RULETYPE_DYNAMICFILTER);
                if (isset($attr2['val'])) {
                    $column->setAttribute('val', (string) $attr2['val']);
                }
                if (isset($attr2['maxVal'])) {
                    $column->setAttribute('maxVal', (string) $attr2['maxVal']);
=======
                $column->createRule()->setRule(
                    '',
                    (string) $filterRule['val'],
                    (string) $filterRule['type']
                )->setRuleType(Rule::AUTOFILTER_RULETYPE_DYNAMICFILTER);
                if (isset($filterRule['val'])) {
                    $column->setAttribute('val', (string) $filterRule['val']);
                }
                if (isset($filterRule['maxVal'])) {
                    $column->setAttribute('maxVal', (string) $filterRule['maxVal']);
>>>>>>> main
                }
            }
        }
    }

    private function readTopTenAutoFilter(?SimpleXMLElement $filterColumn, Column $column): void
    {
        if (isset($filterColumn, $filterColumn->top10)) {
            $column->setFilterType(Column::AUTOFILTER_FILTERTYPE_TOPTENFILTER);
            //    We should only ever have one top10 filter
            foreach ($filterColumn->top10 as $filterRule) {
<<<<<<< HEAD
                $attr2 = $filterRule->attributes() ?? [];
                $column->createRule()->setRule(
                    (
                        ((isset($attr2['percent'])) && ((string) $attr2['percent'] === '1'))
                        ? Rule::AUTOFILTER_COLUMN_RULE_TOPTEN_PERCENT
                        : Rule::AUTOFILTER_COLUMN_RULE_TOPTEN_BY_VALUE
                    ),
                    (string) ($attr2['val'] ?? ''),
                    (
                        ((isset($attr2['top'])) && ((string) $attr2['top'] === '1'))
=======
                $column->createRule()->setRule(
                    (
                        ((isset($filterRule['percent'])) && ((string) $filterRule['percent'] === '1'))
                        ? Rule::AUTOFILTER_COLUMN_RULE_TOPTEN_PERCENT
                        : Rule::AUTOFILTER_COLUMN_RULE_TOPTEN_BY_VALUE
                    ),
                    (string) $filterRule['val'],
                    (
                        ((isset($filterRule['top'])) && ((string) $filterRule['top'] === '1'))
>>>>>>> main
                        ? Rule::AUTOFILTER_COLUMN_RULE_TOPTEN_TOP
                        : Rule::AUTOFILTER_COLUMN_RULE_TOPTEN_BOTTOM
                    )
                )->setRuleType(Rule::AUTOFILTER_RULETYPE_TOPTENFILTER);
            }
        }
    }
}
