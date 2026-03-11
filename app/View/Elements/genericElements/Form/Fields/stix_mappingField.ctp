<?php
// Hidden field to store the serialized JSON mapping data
echo $this->Form->hidden('Feed.settings.stix_mapping', [
    'id' => 'FeedSettingsStixMapping',
    'value' => '',
]);

$attributeFields = [
    'category' => __('Category'),
    'type' => __('Type'),
    'value1' => __('Value1'),
    'value2' => __('Value2'),
    'to_ids' => __('To IDS'),
    'comment' => __('Comment'),
    'first_seen' => __('First Seen'),
    'last_seen' => __('Last Seen'),
];

// Retrieve existing mappings for edit mode
$existingMappings = [];
if (isset($this->request->data['Feed']['settings']['stix_mapping'])) {
    $existingMappings = $this->request->data['Feed']['settings']['stix_mapping'];
}
?>
<div id="stixMappingContainer">
    <label><?php echo __('STIX Custom Field Mapping'); ?></label>
    <p class="clear" style="font-style: italic; color: #888; margin-bottom: 5px;">
        <?php echo __('Map flat STIX field keys to MISP Attribute fields. Values from the STIX source will be placed into the selected Attribute field during conversion.'); ?>
    </p>
    <table id="stixMappingTable" class="table table-condensed" style="width: auto;">
        <thead>
            <tr>
                <th><?php echo __('STIX Field Key'); ?></th>
                <th><?php echo __('MISP Attribute Field'); ?></th>
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
        var attributeFieldOptions = <?php echo json_encode($attributeFields); ?>;
        var existingMappings = <?php echo json_encode($existingMappings); ?>;

        function buildOptionHtml(selectedValue) {
            var html = '<option value=""><?php echo __("-- Select --"); ?></option>';
            for (var key in attributeFieldOptions) {
                if (attributeFieldOptions.hasOwnProperty(key)) {
                    var sel = (key === selectedValue) ? ' selected="selected"' : '';
                    html += '<option value="' + key + '"' + sel + '>' + attributeFieldOptions[key] + '</option>';
                }
            }
            return html;
        }

        function addStixMappingRow(stixKey, attributeField) {
            stixKey = stixKey || '';
            attributeField = attributeField || '';
            var row = '<tr class="stix-mapping-row">' +
                '<td><input type="text" class="form-control stix-mapping-key" value="' + escapeHtml(stixKey) + '" placeholder="<?php echo __("e.g. x_custom_field"); ?>" style="width: 250px;" /></td>' +
                '<td><select class="form-control stix-mapping-field" style="width: 200px;">' + buildOptionHtml(attributeField) + '</select></td>' +
                '<td><span class="btn btn-small btn-danger remove-stix-mapping"><i class="fas fa-trash"></i></span></td>' +
                '</tr>';
            $('#stixMappingRows').append(row);
            serializeStixMappings();
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        function serializeStixMappings() {
            var mappings = [];
            $('#stixMappingRows .stix-mapping-row').each(function() {
                var stixKey = $(this).find('.stix-mapping-key').val().trim();
                var attrField = $(this).find('.stix-mapping-field').val();
                if (stixKey !== '' && attrField !== '') {
                    mappings.push({
                        'stix_key': stixKey,
                        'attribute_field': attrField
                    });
                }
            });
            $('#FeedSettingsStixMapping').val(JSON.stringify(mappings));
        }

        $(document).ready(function() {
            // Load existing mappings
            if (existingMappings && Array.isArray(existingMappings) && existingMappings.length > 0) {
                for (var i = 0; i < existingMappings.length; i++) {
                    addStixMappingRow(existingMappings[i].stix_key || '', existingMappings[i].attribute_field || '');
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
            $('#stixMappingRows').on('change keyup', '.stix-mapping-key, .stix-mapping-field', function() {
                serializeStixMappings();
            });
        });
    })();
</script>