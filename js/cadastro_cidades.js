//file to Create/Update/Delete a city

$(document).ready(function () {
    $('form').on("submit", function (e) {
        e.preventDefault();

        CadastrarCidade(this);
    });

    $('#nome').focusout(function() {
        MostrarCidade(this);
    });

    $('#deletar').click(function() {
        ExcluirCidade();
    });
    
});

//get a city in database
function MostrarCidade(elem) {

    if($(elem).val() == '') {
        $("#id").val('');
        $("#latitude").val('');
        $("#longitude").val('');
        $("#deletar").css("display", "none");
        return;
    }

    $.ajax({
        async: true,
        dataType: "json", 
        method: "POST",
        url: "../php/cadastro_cidades.php",
        data: "tipo=Mostrar&cidade=" + $(elem).val(),
    
        success: function(data) {
            if(data) {
                $("#id").val(data['ID']);
                $("#latitude").val(data['LATITUDE']);
                $("#longitude").val(data['LONGITUDE']);
                $("#deletar").css("display", "block");
            } else {
                $("#id").val('');
                $("#latitude").val('');
                $("#longitude").val('');
                $("#deletar").css("display", "none");
            }
        }
    });
}

//update/insert a city
function CadastrarCidade(form) {

    $.ajax({
        async: true,
        dataType: "json", 
        method: "POST",
        url: "../php/cadastro_cidades.php",
        data: "tipo=Cadastrar&" + $(form).serialize(),
    
        success: function(data) {
            alert(data['msg']);
        }
    });

}

//"delete" a city
function ExcluirCidade() {

    //confirming the "delete"
    if(confirm('Deseja realmente excluir essa cidade?')) {

        $.ajax({
            async: true,
            dataType: "json", 
            method: "POST",
            url: "../php/cadastro_cidades.php",
            data: "tipo=Excluir&ID=" + $("#id").val(),
        
            success: function(data) {
                //show message
                if(data['erro'] == 0) {
                    $("#id").val('');
                    $("#nome").val('');
                    $("#latitude").val('');
                    $("#longitude").val('');
                    $("#deletar").css("display", "none");
                }

                alert(data['msg']);
            }
        });
    }

    
}