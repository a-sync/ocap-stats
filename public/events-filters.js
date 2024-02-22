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

const events_filters = document.getElementById('events-filters');

if (events_filters && entities && sides && events_num) {
    const tab_bar = document.querySelector('#events-table .mdc-tab-bar');
    const tabs = tab_bar ? tab_bar.querySelectorAll('.mdc-tab') : [];
    function update_tabs () {
        return !tab_bar ? () => {} : () => {
            for (const t of tabs) {
                if (attacker_ss.settings.isOpen || victim_ss.settings.isOpen || event_ss.settings.isOpen) {
                    t.style.opacity = '0.5';
                    t.style.pointerEvents = 'none';
                } else {
                    t.style.opacity = '1';
                    t.style.pointerEvents = 'auto';
                }
            }
        };
    }

    const attacker_filter = document.getElementById('attacker-filter');
    const victim_filter = document.getElementById('victim-filter');
    const event_filter = document.getElementById('event-filter');

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
    
    const attacker_select = document.createElement('select');
    attacker_select.classList.add('attacker-filter-ss');
    attacker_filter.appendChild(attacker_select);
    const attacker_ss = new SlimSelect({
        'select': attacker_select,
        'settings': {
            showSearch: true,
            allowDeselect: true,
            closeOnSelect: false
        },
        'data': [
            {
                text: '',
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
                update_events_ss_dataset();
                update_events_table_classes();
            },
            beforeOpen: update_tabs(),
            afterClose: update_tabs()
        }
    });

    const victim_select = document.createElement('select');
    victim_select.classList.add('victim-filter-ss');
    victim_filter.appendChild(victim_select);
    const victim_ss = new SlimSelect({
        'select': victim_select,
        'settings': {
            showSearch: true,
            allowDeselect: true,
            closeOnSelect: false
        },
        'data': [
            {
                text: '',
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
                update_events_ss_dataset();
                update_events_table_classes();
            },
            beforeOpen: update_tabs(),
            afterClose: update_tabs()
        }
    });

    const ss_events_data_field = Object.keys(events_num).map(ev => {
        return {
            text: ev + ' (' + events_num[ev] + ')',
            value: ev
        }
    });

    const event_select = document.createElement('select');
    event_select.multiple = true;
    event_select.classList.add('event-filter-ss');
    event_filter.appendChild(event_select);
    const event_ss = new SlimSelect({
        'select': event_select,
        'settings': {
            showSearch: true,
            allowDeselect: true,
            closeOnSelect: false
        },
        'data': [
            {
                text: '',
                placeholder: true
            },
            ...ss_events_data_field
        ],
        events: {
            afterChange: () => {
                update_events_table_classes();
            },
            beforeOpen: update_tabs(),
            afterClose: update_tabs()
        }
    });

    function update_events_ss_dataset () {
        if (!event_ss) return;

        const rules = [];
        const attacker_ss_value = attacker_ss.getSelected();
        if (attacker_ss_value.length && attacker_ss_value[0] !== '') {
            const attacker_id = attacker_ss_value[0] === 'null' ? '' : attacker_ss_value[0];
            rules.push('[data-attacker-id="' + attacker_id + '"]');
        }

        const victim_ss_value = victim_ss.getSelected();
        if (victim_ss_value.length && victim_ss_value[0] !== '') {
            const victim_id = victim_ss_value[0] === 'null' ? '' : victim_ss_value[0];
            rules.push('[data-victim-id="' + victim_id + '"]');
        }

        const event_ss_value = event_ss.getSelected();

        const ss_events_data_field_new = Object.keys(events_num).map(ev => {
            const count = document.querySelectorAll('#events-table tbody tr[data-event-name="' + ev + '"]' + rules.join('')).length;
            return {
                text: ev + ' (' + count + ')',
                value: ev,
                selected: event_ss_value.includes(ev)
            }
        });

        event_ss.setData(ss_events_data_field_new);
    }

    function update_events_table_classes() {
        let attacker_id = false;
        const attacker_ss_value = attacker_ss.getSelected();
        if (attacker_ss_value.length && attacker_ss_value[0] !== '') {
            attacker_id = attacker_ss_value[0] === 'null' ? '' : attacker_ss_value[0];
        }

        let victim_id = false;
        const victim_ss_value = victim_ss.getSelected();
        if (victim_ss_value.length && victim_ss_value[0] !== '') {
            victim_id = victim_ss_value[0] === 'null' ? '' : victim_ss_value[0];
        }

        const event_ss_value = event_ss.getSelected();

        const rows = document.querySelectorAll('#events-table tbody tr');
        for (const tr of rows) {
            if (event_ss_value.length > 0) {
                if (!event_ss_value.includes(tr.dataset.eventName)) {
                    tr.classList.add('dnone');
                    continue;
                }
            }

            if (attacker_id !== false && attacker_id !== tr.dataset.attackerId) {
                tr.classList.add('dnone');
                continue;
            }

            if (victim_id !== false && victim_id !== tr.dataset.victimId) {
                tr.classList.add('dnone');
                continue;
            }

            tr.classList.remove('dnone');
        }
    }

    setTimeout(()=>{events_filters.classList.remove('dnone');},20);
}