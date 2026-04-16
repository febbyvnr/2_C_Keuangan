<?php

namespace PhpOffice\PhpSpreadsheet\Reader\Ods;

use PhpOffice\PhpSpreadsheet\Document\Properties as DocumentProperties;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use SimpleXMLElement;

class Properties
{
<<<<<<< HEAD
    private Spreadsheet $spreadsheet;
=======
    /** @var Spreadsheet */
    private $spreadsheet;
>>>>>>> main

    public function __construct(Spreadsheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
    }

<<<<<<< HEAD
    /** @param array{meta?: string, office?: string, dc?: string} $namespacesMeta */
    public function load(SimpleXMLElement $xml, array $namespacesMeta): void
    {
        $docProps = $this->spreadsheet->getProperties();
        $officeProperty = $xml->children($namespacesMeta['office'] ?? '');
        foreach ($officeProperty as $officePropertyData) {
            if (isset($namespacesMeta['dc'])) {
=======
    public function load(SimpleXMLElement $xml, array $namespacesMeta): void
    {
        $docProps = $this->spreadsheet->getProperties();
        $officeProperty = $xml->children($namespacesMeta['office']);
        foreach ($officeProperty as $officePropertyData) {
            if (isset($namespacesMeta['dc'])) {
                /** @scrutinizer ignore-call */
>>>>>>> main
                $officePropertiesDC = $officePropertyData->children($namespacesMeta['dc']);
                $this->setCoreProperties($docProps, $officePropertiesDC);
            }

            $officePropertyMeta = null;
            if (isset($namespacesMeta['dc'])) {
<<<<<<< HEAD
                $officePropertyMeta = $officePropertyData->children($namespacesMeta['meta'] ?? '');
=======
                /** @scrutinizer ignore-call */
                $officePropertyMeta = $officePropertyData->children($namespacesMeta['meta']);
>>>>>>> main
            }
            $officePropertyMeta = $officePropertyMeta ?? [];
            foreach ($officePropertyMeta as $propertyName => $propertyValue) {
                $this->setMetaProperties($namespacesMeta, $propertyValue, $propertyName, $docProps);
            }
        }
    }

    private function setCoreProperties(DocumentProperties $docProps, SimpleXMLElement $officePropertyDC): void
    {
        foreach ($officePropertyDC as $propertyName => $propertyValue) {
            $propertyValue = (string) $propertyValue;
            switch ($propertyName) {
                case 'title':
                    $docProps->setTitle($propertyValue);

                    break;
                case 'subject':
                    $docProps->setSubject($propertyValue);

                    break;
                case 'creator':
                    $docProps->setCreator($propertyValue);
                    $docProps->setLastModifiedBy($propertyValue);

                    break;
                case 'date':
                    $docProps->setModified($propertyValue);

                    break;
                case 'description':
                    $docProps->setDescription($propertyValue);

                    break;
            }
        }
    }

<<<<<<< HEAD
    /** @param array{meta?: string, office?: mixed, dc?: mixed} $namespacesMeta */
=======
>>>>>>> main
    private function setMetaProperties(
        array $namespacesMeta,
        SimpleXMLElement $propertyValue,
        string $propertyName,
        DocumentProperties $docProps
    ): void {
<<<<<<< HEAD
        $propertyValueAttributes = $propertyValue->attributes($namespacesMeta['meta'] ?? '');
=======
        $propertyValueAttributes = $propertyValue->attributes($namespacesMeta['meta']);
>>>>>>> main
        $propertyValue = (string) $propertyValue;
        switch ($propertyName) {
            case 'initial-creator':
                $docProps->setCreator($propertyValue);

                break;
            case 'keyword':
                $docProps->setKeywords($propertyValue);

                break;
            case 'creation-date':
                $docProps->setCreated($propertyValue);

                break;
            case 'user-defined':
<<<<<<< HEAD
                $name2 = (string) ($propertyValueAttributes['name'] ?? '');
                if ($name2 === 'Company') {
                    $docProps->setCompany($propertyValue);
                } elseif ($name2 === 'category') {
                    $docProps->setCategory($propertyValue);
                } else {
                    $this->setUserDefinedProperty($propertyValueAttributes, $propertyValue, $docProps);
                }
=======
                $this->setUserDefinedProperty($propertyValueAttributes, $propertyValue, $docProps);
>>>>>>> main

                break;
        }
    }

<<<<<<< HEAD
    /** @param iterable<string> $propertyValueAttributes */
    private function setUserDefinedProperty(iterable $propertyValueAttributes, string $propertyValue, DocumentProperties $docProps): void
=======
    /**
     * @param mixed $propertyValueAttributes
     * @param mixed $propertyValue
     */
    private function setUserDefinedProperty($propertyValueAttributes, $propertyValue, DocumentProperties $docProps): void
>>>>>>> main
    {
        $propertyValueName = '';
        $propertyValueType = DocumentProperties::PROPERTY_TYPE_STRING;
        foreach ($propertyValueAttributes as $key => $value) {
            if ($key == 'name') {
<<<<<<< HEAD
                /** @var scalar $value */
                $propertyValueName = (string) $value;
            } elseif ($key == 'value-type') {
                /** @var string $value */
=======
                $propertyValueName = (string) $value;
            } elseif ($key == 'value-type') {
>>>>>>> main
                switch ($value) {
                    case 'date':
                        $propertyValue = DocumentProperties::convertProperty($propertyValue, 'date');
                        $propertyValueType = DocumentProperties::PROPERTY_TYPE_DATE;

                        break;
                    case 'boolean':
                        $propertyValue = DocumentProperties::convertProperty($propertyValue, 'bool');
                        $propertyValueType = DocumentProperties::PROPERTY_TYPE_BOOLEAN;

                        break;
                    case 'float':
                        $propertyValue = DocumentProperties::convertProperty($propertyValue, 'r4');
                        $propertyValueType = DocumentProperties::PROPERTY_TYPE_FLOAT;

                        break;
                    default:
                        $propertyValueType = DocumentProperties::PROPERTY_TYPE_STRING;
                }
            }
        }

        $docProps->setCustomProperty($propertyValueName, $propertyValue, $propertyValueType);
    }
}
