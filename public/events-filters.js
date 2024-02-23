function deepMerge(obj1, obj2) {
    const merged = {};
    for (const key in obj1) {
        if (obj1.hasOwnProperty(key)) {

            if (Array.isArray(obj1[key])) {
                merged[key] = [...obj1[key], ...obj2[key] || []];
            } else if (typeof obj1[key] === 'object') {
                merged[key] = deepMerge(obj1[key], obj2[key] || {});
            } else {
                merged[key] = obj1[key];
            }
        }
    }
    for (const key in obj2) {
        if (obj2.hasOwnProperty(key) && !merged.hasOwnProperty(key)) {
            merged[key] = obj2[key];
        }
    }
    return merged;
}

function init_events_filters(entities, sides) {
    const events_filters = document.getElementById('events-filters');

    const tab_bar = document.querySelector('#events-table .mdc-tab-bar');
    const tabs = tab_bar ? tab_bar.querySelectorAll('.mdc-tab') : [];
    function update_tabs () {
        return !tab_bar ? () => {} : () => {
            for (const t of tabs) {
                if (entity_ss.settings.isOpen || event_ss.settings.isOpen) {
                    t.style.opacity = '0.5';
                    t.style.pointerEvents = 'none';
                } else {
                    t.style.opacity = '1';
                    t.style.pointerEvents = 'auto';
                }
            }
        };
    }

    const side_player_entities = {};
    const side_other_entities = {};
    for (const ent of entities) {
        if (parseInt(ent.is_player, 10) === 1) {
            if (!side_player_entities[ent.side]) {
                side_player_entities[ent.side] = [{
                    text: '=== 👤 ===',
                    disabled: true
                }];
            }
            side_player_entities[ent.side].push({
                text: '#' + ent.id + ' ' + ent.name,
                value: ent.id
            });
        } else {
            if (!side_other_entities[ent.side]) {
                side_other_entities[ent.side] = [{
                    text: '=== 🤖🚓🚁🔫 ===',
                    disabled: true
                }];
            }
            side_other_entities[ent.side].push({
                text: '#' + ent.id + ' ' + ent.name,
                value: ent.id
            });
        }
    }

    const side_entities = deepMerge(side_player_entities, side_other_entities);
    const ss_entities_data_field = Object.keys(side_entities).map(side => {
        return {
            label: sides[side] || '❓',
            options: side_entities[side],
            closable: 'open'
        }
    });
    
    const entity_select = document.createElement('select');
    entity_select.multiple = true;
    entity_select.classList.add('entity-filter-ss');
    events_filters.appendChild(entity_select);
    const entity_ss = new SlimSelect({
        select: entity_select,
        settings: {
            showSearch: true,
            allowDeselect: true,
            closeOnSelect: false
        },
        data: [
            {
                text: 'Filter by entities',
                value: '',
                placeholder: true
            },
            {
                text: 'nobody / "something"',
                value: 'null'
            },
            ...ss_entities_data_field
        ],
        events: {
            afterChange: () => {
                update_filters_dataset(true);
            },
            beforeOpen: update_tabs(),
            afterClose: update_tabs()
        }
    });

    const event_select = document.createElement('select');
    event_select.multiple = true;
    event_select.classList.add('event-filter-ss');
    events_filters.appendChild(event_select);
    const event_ss = new SlimSelect({
        select: event_select,
        settings: {
            showSearch: true,
            allowDeselect: true,
            closeOnSelect: false
        },
        data: [
            {
                text: 'Filter by events',
                value: '',
                placeholder: true
            }
        ],
        events: {
            afterChange: () => {
                update_filters_dataset(false);
            },
            beforeOpen: update_tabs(),
            afterClose: update_tabs()
        }
    });

    function update_filters_dataset(update_events) {
        if (!entity_ss || !event_ss) return;

        const entity_ss_value = entity_ss.getSelected().map(e => {
            return e === 'null' ? '' : e;
        });
        const event_ss_value = event_ss.getSelected();

        const events_num = [];
        const ss_events_data_field = [
            {
                text: 'Filter by events',
                value: '',
                placeholder: true
            }
        ];

        const rows = document.querySelectorAll('#events-table tbody tr');
        for (const tr of rows) {
            const ev = tr.dataset.eventName;
            if (update_events && events_num[ev] === undefined) { 
                const rules = [];
                if (entity_ss_value.length > 0) {
                    for (const entity_id of entity_ss_value) {
                        rules.push('#events-table tbody tr[data-event-name="' + ev + '"][data-attacker-id="' + entity_id + '"]');
                        rules.push('#events-table tbody tr[data-event-name="' + ev + '"][data-victim-id="' + entity_id + '"]');
                    }
                } else {
                    rules.push('#events-table tbody tr[data-event-name="' + ev + '"]');
                }

                const count = document.querySelectorAll(rules.join(',')).length;
                ss_events_data_field.push({
                    text: ev + ' (' + count + ')',
                    value: ev,
                    selected: event_ss_value.includes(ev)
                });

                events_num[ev] = count;
            }

            if (entity_ss_value.length > 0 && !entity_ss_value.includes(tr.dataset.attackerId) && !entity_ss_value.includes(tr.dataset.victimId)) {
                tr.classList.add('dnone');
            } else if (event_ss_value.length > 0 && !event_ss_value.includes(tr.dataset.eventName)) {
                tr.classList.add('dnone');
            } else {
                tr.classList.remove('dnone');
            }
        }

        if (update_events) event_ss.setData(ss_events_data_field);
    }

    update_filters_dataset(true);
    events_filters.classList.remove('dnone');
}