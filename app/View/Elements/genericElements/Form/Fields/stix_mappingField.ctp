<?php
// Build the wrapper div attributes from params
$divAttrs = '';
if (!empty($params['div'])) {
    if (is_array($params['div'])) {
        foreach ($params['div'] as $attrName => $attrVal) {
            $divAttrs .= ' ' . h($attrName) . '="' . h($attrVal) . '"';
        }
    }
}
?>
<div<?php echo $divAttrs; ?>>
    <label><?php echo __('STIX Custom Field Mapping'); ?></label>
    <?php
    // Hidden field to store the serialized JSON mapping data
    echo $this->Form->hidden('Feed.settings.stix_mapping', [
        'id' => 'FeedSettingsStixMapping',
        'value' => '',
    ]);

    // Retrieve existing mappings for edit mode
    $existingMappings = [];
    if (isset($this->request->data['Feed']['settings']['stix_mapping'])) {
        $existingMappings = $this->request->data['Feed']['settings']['stix_mapping'];
    }
    ?>
    <p class="clear" style="font-style: italic; color: #888; margin-bottom: 5px;">
        <?php echo __('Override MISP event fields with static values for all events created by this STIX feed.'); ?>
    </p>
    <table id="stixMappingTable" class="table table-condensed" style="width: auto;">
        <thead>
            <tr>
                <th><?php echo __('MISP Event Field'); ?></th>
                <th><?php echo __('Value'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="stixMappingRows">
        </tbody>
    </table>
    <span id="addStixMappingRow" class="btn btn-small btn-inverse" style="margin-bottom: 10px;">
        <i class="fas fa-plus"></i> <?php echo __('Add Mapping'); ?>
    </span>
    </div>

    <script type="text/javascript">
        (function() {
            var existingMappings = <?php echo json_encode($existingMappings); ?>;

            function escapeHtml(str) {
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }

            function addStixMappingRow(mispField, value) {
                mispField = mispField || '';
                value = value || '';
                var row = '<tr class="stix-mapping-row">' +
                    '<td><input type="text" class="form-control stix-mapping-misp-field" value="' + escapeHtml(mispField) + '" placeholder="<?php echo __("e.g. info, extends_uuid"); ?>" style="width: 250px;" /></td>' +
                    '<td><input type="text" class="form-control stix-mapping-value" value="' + escapeHtml(value) + '" placeholder="<?php echo __("Static value to set"); ?>" style="width: 250px;" /></td>' +
                    '<td><span class="btn btn-small btn-danger remove-stix-mapping"><i class="fas fa-trash"></i></span></td>' +
                    '</tr>';
                $('#stixMappingRows').append(row);
                serializeStixMappings();
            }

            function serializeStixMappings() {
                var mappings = [];
                $('#stixMappingRows .stix-mapping-row').each(function() {
                    var mispField = $(this).find('.stix-mapping-misp-field').val().trim();
                    var value = $(this).find('.stix-mapping-value').val().trim();
                    if (mispField !== '') {
                        mappings.push({
                            'misp_field': mispField,
                            'value': value
                        });
                    }
                });
                $('#FeedSettingsStixMapping').val(JSON.stringify(mappings));
            }

            $(document).ready(function() {
                // Load existing mappings
                if (existingMappings && Array.isArray(existingMappings) && existingMappings.length > 0) {
                    for (var i = 0; i < existingMappings.length; i++) {
                        addStixMappingRow(existingMappings[i].misp_field || '', existingMappings[i].value || '');
                    }
                }

                // Add row button
                $('#addStixMappingRow').on('click', function() {
                    addStixMappingRow('', '');
                });

                // Remove row button (delegated)
                $('#stixMappingRows').on('click', '.remove-stix-mapping', function() {
                    $(this).closest('tr').remove();
                    serializeStixMappings();
                });

                // Serialize on any input change
                $('#stixMappingRows').on('change keyup', '.stix-mapping-misp-field, .stix-mapping-value', function() {
                    serializeStixMappings();
                });
            });
        })();
    </script>