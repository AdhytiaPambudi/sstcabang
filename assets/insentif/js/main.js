$( document ).ready(function() {
    {% for data in data %}
        {% if data.KodePerkiraan == '1141.01' %}
            {% if data.Cabang == 'Aceh' %}
    			document.getElementById("pembelianAceh").innerHTML = "{{ data[0].Debit | number_format}}";
    		{% endif %}
    	{% endif %}
    {% endfor %}
    }
});