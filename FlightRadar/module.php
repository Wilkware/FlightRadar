<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/_traits.php';  // Generell funktions

// CLASS FlightRadar
class FlightRadar extends IPSModuleStrict
{
    use DebugHelper;
    use FormatHelper;

    /**
     * @var string MQTT Splitter Modul ID
     */
    private const GUID_MQTT_IO = '{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}';  // Splitter

    /**
     * In contrast to Construct, this function is called only once when creating the instance and starting IP-Symcon.
     * Therefore, status variables and module properties which the module requires permanently should be created here.
     *
     * @return void
     */
    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        // Device-Topic (Name)
        $this->RegisterPropertyString('MQTTBaseTopic', 'flights');
        $this->RegisterPropertyString('MQTTTopic', '');

        // Time control
        $this->RegisterPropertyInteger('ExpiryMinutes', 5);

        // Attribute for saved flight data
        $this->RegisterAttributeString('FlightData', '[]');

        if ((float) IPS_GetKernelVersion() < 8.2) {
            $this->ConnectParent(self::GUID_MQTT_IO);
        }

        // Set visualization type to 1, as we want to offer HTML
        $this->SetVisualizationType(1);
    }

    /**
     * This function is called when deleting the instance during operation and when updating via "Module Control".
     * The function is not called when exiting IP-Symcon.
     *
     * @return void
     */
    public function Destroy(): void
    {
        parent::Destroy();
    }

    /**
     * The content can be overwritten in order to transfer a self-created configuration page.
     * This way, content can be generated dynamically.
     * In this case, the "form.json" on the file system is completely ignored.
     *
     * @return string Content of the configuration page.
     */
    public function GetConfigurationForm(): string
    {
        // Get Form
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

        // Extract Version
        $ins = IPS_GetInstance($this->InstanceID);
        $mod = IPS_GetModule($ins['ModuleInfo']['ModuleID']);
        $lib = IPS_GetLibrary($mod['LibraryID']);
        $form['actions'][0]['items'][2]['caption'] = sprintf('v%s.%d', $lib['Version'], $lib['Build']);

        // Debug output
        // $this->LogDebug(__FUNCTION__, $form);
        return json_encode($form);
    }

    /**
     * Is executed when "Apply" is pressed on the configuration page and immediately after the instance has been created.
     *
     * @return void
     */
    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        $base = $this->ReadPropertyString('MQTTBaseTopic');
        $topic = $this->ReadPropertyString('MQTTTopic');

        // Check setup
        if (empty($base) || empty($topic)) {
            $this->SetStatus(201);
            return;
        } else {
            // Set filter
            $filter = preg_quote($this->ReadPropertyString('MQTTBaseTopic') . '/' . $this->ReadPropertyString('MQTTTopic'));
            $this->LogDebug(__FUNCTION__, 'Filter: .*' . $filter . '.*');
            $this->SetReceiveDataFilter('.*' . $filter . '.*');
        }

        // Set status
        $this->SetStatus(102);
    }

    /**
     * This function is called by IP-Symcon and processes sent data and, if necessary, forwards it to
     * all child instances. Data can be sent using the SendDataToChildren function.
     *
     * @param string $json Data package in JSON format
     *
     * @return string Optional response to the parent instance
     */
    public function ReceiveData(string $json): string
    {
        $data = json_decode($json);

        $topic = $data->Topic;
        $payload = hex2bin($data->Payload);
        $this->LogDebug(__FUNCTION__, 'Received Topic: ' . $topic . ' Payload: ' . $payload);

        $flight = json_decode($payload, true);
        if ($flight !== null && isset($flight['flight'])) {
            // Neuen Flug hinzufügen
            $this->AddOrUpdateFlight($flight);
            $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
        }
        return '';
    }

    /**
     * Is called when, for example, a button is clicked in the visualization.
     *
     * @param string $ident Ident of the variable
     * @param mixed $value The value to be set
     *
     * @return void
     */
    public function RequestAction(string $ident, mixed $value): void
    {
        // Debug output
        $this->LogDebug(__FUNCTION__, $ident . ' => ' . $value);
        // TODO: Replace identifier
        switch ($ident) {
            case 'OnXxxxxYyyyy':
                break;
            default:
                $this->LogDebug(__FUNCTION__, 'There was no reaction to the action.');
        }
        // Send a complete update message to the display, as parameters may have changed
        // $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
        return;
    }

    /**
     * If the HTML-SDK is to be used, this function must be overwritten in order to return the HTML content.
     *
     * @return string Initial display of a representation via HTML SDK
     */
    public function GetVisualizationTile(): string
    {
        // Add a script to set the values when loading, analogous to changes at runtime
        // Although the return from GetFullUpdateMessage is already JSON-encoded, json_encode is still executed a second time
        // This adds quotation marks to the string and any quotation marks within it are escaped correctly
        $handling = '<script>handleMessage(' . json_encode($this->GetFullUpdateMessage()) . ');</script>';
        // Add static HTML from file
        $module = file_get_contents(__DIR__ . '/module.html');
        // Important: $initialHandling at the end, as the handleMessage function is only defined in the HTML
        return $module . $handling;
    }

    /**
     * Generate a message that updates all elements in the HTML display.
     *
     * @return string JSON encoded message information
     */
    private function GetFullUpdateMessage(): string
    {
        // Prepare result
        $result = [];

        // Get flight data
        $flights = $this->CleanupExpiredFlights();
        $this->LogDebug(__FUNCTION__, print_r($flights, true));
        if (!empty($flights)) {
            /* sort by timestamp
            usort($flights, function ($a, $b)
            {
                $timeA = $a['estimated']['arrival'] ?? $a['scheduled']['arrival'] ?? 0;
                $timeB = $b['estimated']['arrival'] ?? $b['scheduled']['arrival'] ?? 0;
                // "N/A" zu 0, sonst timestamp
                $timeA = ($timeA === 'N/A') ? 0 : $timeA;
                $timeB = ($timeB === 'N/A') ? 0 : $timeB;
                return $timeA - $timeB;
            });
             */
            usort($flights, function ($a, $b)
            {
                return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
            });
            $result['flights'] = $flights;
        }
        $this->LogDebug(__FUNCTION__, print_r($result, true));
        // send it
        return json_encode($result);
    }

    /**
     * Add or update a flight.
     *
     * @param array<string,mixed> $flight Flight data as associative array
     *
     * @return void
     */
    private function AddOrUpdateFlight(array $flight): void
    {
        $flights = json_decode($this->ReadAttributeString('FlightData'), true);
        if (!is_array($flights)) {
            $flights = [];
        }

        // get the id
        $id = $flight['flight'];

        // add or update flight
        $flights[$id] = $flight;

        // save
        $this->WriteAttributeString('FlightData', json_encode($flights));
        $this->LogDebug(__FUNCTION__, json_encode($flights));

        // logging
        $this->LogDebug(__FUNCTION__, $id . ' added/updated.');
    }

    /**
     * Cleanup expired flights
     *
     * @return array<string,mixed> Updated flight data
     */
    private function CleanupExpiredFlights(): array
    {
        $flights = json_decode($this->ReadAttributeString('FlightData'), true);
        if (!is_array($flights) || empty($flights)) {
            return [];
        }

        $expiry = $this->ReadPropertyInteger('ExpiryMinutes') * 60;
        $current = time();
        $changed = false;

        foreach ($flights as $id => $flight) {
            $this->LogDebug(__FUNCTION__, 'ID:' . $id);
            $received = strtotime($flight['timestamp'] ?? 0);

            // Wenn Flug abgelaufen ist, entfernen
            if (($current - $received) > $expiry) {
                unset($flights[$id]);
                $changed = true;
                $this->LogDebug(__FUNCTION__, 'FlightExpired: ' . $id);
            }
        }

        if ($changed) {
            $this->WriteAttributeString('FlightData', json_encode($flights));
        }
        return $flights;
    }

    /**
     * Show message via popup
     *
     * @param string $caption echo message
     *
     * @return void
     */
    //private function EchoMessage(string $caption): void
    //{
    //    $this->UpdateFormField('EchoMessage', 'caption', $this->Translate($caption));
    //    $this->UpdateFormField('EchoPopup', 'visible', true);
    //}
}