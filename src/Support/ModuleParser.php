<?php

namespace PanicDev\Archium\Support;

use SimpleXMLElement;

class ModuleParser
{
    /**
     * Parse the archium modules XML content.
     */
    public static function parse(string $xmlContent): array
    {
        try {
            // Remove archium:: namespace from the content
            $xmlContent = str_replace(['<archium::', '</archium::', '<archium', '</archium'], ['<', '</', '<', '</'], $xmlContent);
            
            $xml = new \SimpleXMLElement($xmlContent);
            $modules = [];

            foreach ($xml->module as $moduleNode) {
                $key = (string) $moduleNode->key;
                
                // Parse dependencies and depends arrays
                $dependencies = [];
                if (isset($moduleNode->dependencies->module)) {
                    foreach ($moduleNode->dependencies->module as $dep) {
                        $dependencies[] = (string) $dep;
                    }
                }

                $depends = [];
                if (isset($moduleNode->depends->module)) {
                    foreach ($moduleNode->depends->module as $dep) {
                        $depends[] = (string) $dep;
                    }
                }

                $modules[$key] = [
                    'key' => $key,
                    'name' => (string) $moduleNode->name,
                    'directory' => (string) $moduleNode->directory,
                    'description' => (string) $moduleNode->description,
                    'repository' => (string) $moduleNode->repository,
                    'branch' => (string) ($moduleNode->branch ?? 'main'),
                    'safe' => (string) $moduleNode->safe === 'true',
                    'version' => (string) $moduleNode->version,
                    'required' => (string) $moduleNode->required === 'true',
                    'dependencies' => $dependencies,
                    'depends' => $depends,
                ];
            }

            return $modules;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to parse archium modules XML: ' . $e->getMessage());
        }
    }
} 