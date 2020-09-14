//file to calculate distance and to show all the possibles routes
$(document).ready(function () {
    $('form').on("submit", function (e) {
        e.preventDefault();

        CalcularCidades(this);
    });
});

function CalcularCidades(form) {

    $.ajax({
        async: true,
        dataType: "json", 
        method: "POST",
        url: "../php/ligacoes_cidades.php",
        data: $(form).serialize(),
    
        success: function(data) {
            if(data['erro'] !== undefined) {
                //show error message
                alert(data['msg']);
                $("#ligacoes").text('');
                $("#lblligacoes").text("Percurso");
            } else {
                //show the text with the cities and distance
                var texto = "";
                var totalPercorrido = 0;

                for(var i = 0; i < data.length; i++) {
                    texto += "Cidades: " + data[i]['cidade1'] + " ~ " + data[i]['cidade2'] + "\n";
                    texto += "Distância: " + data[i]['distancia'] + "\n\n";
                    totalPercorrido += data[i]['distancia'];
                }
                
                //show the total distance
                $("#ligacoes").text(texto);
                $("#lblligacoes").text("Percurso - Total percorrido : " + totalPercorrido);
            }
        }
    });
}