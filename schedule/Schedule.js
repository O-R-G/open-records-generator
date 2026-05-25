class Schedule{
    constructor(container, config, siblings, existingAction){
        this.container = container;
        this.config = config;
        this.actions = this.config.actions;
        this.siblings = siblings;
        this.existingAction = existingAction;
        this.currentValue = this.existingAction ? this.existingAction.action : '';
        this.select = null;
        this.datetime_section = null;
        this.datetime_input = null;
        this.record_to_replace_section = null;
        this.record_to_replace_select = null;
        this.options = [];
        this.init();
        
    }
    init(){
        this.render();
        this.updateSectionStates(this.currentValue);
        this.addListeners();
    }
    render(){
        this.select = document.createElement('select');
        this.select.name = this.config.name;
        this.select.className = 'full-width';
        this.select.setAttribute('form', 'edit-form');
        this.select.value = this.currentValue;
        // let selectedIndex = 0;
        for(let i = 0 ; i < this.actions.length; i++) {
            const action = this.actions[i];
            const selected = action.value === this.currentValue;
            let option = this.renderOption(action.display, action.value, selected);
            this.options.push( option );
            this.select.append( option );
        }

        let input_id = "scheduled-datetime";
        let existing_value = this.existingAction && this.existingAction['datetime'] ? this.existingAction['datetime'] : '';
        this.datetime_section = document.createElement('div');
        this.datetime_section.id = input_id + '-container';
        this.datetime_section.className = 'dontdisplay';
        this.datetime_section.innerHTML = '<input form="edit-form" id="'+input_id+'" name="'+input_id+'" type="text" placeholder="e.g., 2000-01-01 12:00:00" value="'+existing_value+'" />';
        this.datetime_input = this.datetime_section.querySelector('input');

        input_id = "record-to-replace";
        existing_value = this.existingAction && this.existingAction['record-to-replace'] ? this.existingAction['record-to-replace'] : '';
        this.record_to_replace_section = document.createElement('div');
        this.record_to_replace_section.id = input_id + '-container';
        this.record_to_replace_section.className = 'dontdisplay';
        this.record_to_replace_section.innerHTML = '<select form="edit-form" id="'+input_id+'" name="'+input_id+'" class="full-width">' +(this.siblings.reduce((carry, item)=>{ 
            let selected = existing_value == item['id'] ? 'selected' : '';
            return carry + '<option '+selected+' value="'+item['id']+'">'+item['name1']+'</option>' 
        }, ''))+ '</select>';
        this.record_to_replace_select = this.record_to_replace_section.querySelector('select');
        this.container.innerHTML = 'Action<br>';
        this.container.append(this.select, this.datetime_section, this.record_to_replace_section);
    }
    renderOption(display, value, selected){
        const output = document.createElement('option');
        output.value = value;
        output.innerHTML = display;
        output.selected = selected;
        return output;
    }
    addListeners(){
        this.select.addEventListener('change', ()=>{
            this.currentValue = this.select.value;
            this.updateSectionStates(this.currentValue);
        })
    }
    updateSectionStates(value){
        if(value === '') {
            this.datetime_section.classList.add('dontdisplay');
            this.datetime_input.disabled = true;
            this.record_to_replace_section.classList.add('dontdisplay');
            this.record_to_replace_select.disabled = true;
        } else if(value === 'publish') {
            this.datetime_section.classList.remove('dontdisplay');
            this.datetime_input.disabled = false;
            this.record_to_replace_section.classList.add('dontdisplay');
            this.record_to_replace_select.disabled = true;
        } else if(value === 'publish-and-replace') {
            this.datetime_section.classList.remove('dontdisplay');
            this.datetime_input.disabled = false;
            this.record_to_replace_section.classList.remove('dontdisplay');
            this.record_to_replace_select.disabled = false;
        }
    }
}