// var canvas = document.getElementById("myCanvas");
// var ctx = canvas.getContext("2d");

// trouver la solution d'un système de 2 equations de la forme  
//	e[0]*x + e[1]*y = e[2]
function LP_cramer2( e1, e2 ) {
var det = e1[0] * e2[1] - e1[1] * e2[0];
if	( det == 0.0 )
	return [ 0, 0 ];
var sol = new Array();
sol[0] = ( e1[2] * e2[1] - e1[1] * e2[2] ) / det;
sol[1] = ( e1[0] * e2[2] - e1[2] * e2[0] ) / det;
return sol;
}

// trouver le peak du secteur, avec un offset off 
function LP_peak( ang0, ang1, off ) {
var cos0 = Math.cos( ang0 );
var sin0 = Math.sin( ang0 );
var cos1 = Math.cos( ang1 );
var sin1 = Math.sin( ang1 );
// les deux droites du secteur avant translation
// de la forme d[0]*x + d[1]*y = d[2]
var d0 = [ sin0, -cos0, 0 ];
var d1 = [ sin1, -cos1, 0 ];
// vecteurs de translation des 2 droites
x0 = off * -sin0; y0 = off *  cos0;	// off @ ang0 + pi/2 "vers l'interieur" du secteur
x1 = off *  sin1; y1 = off * -cos1;	// off @ ang1 - pi/2
// translation
// d0[2] = d0[0]* x0 + d0[1]* y0 + d0[2];	// <-- formule avant simplification
   d0[2] = sin0 * x0 - cos0 * y0;	// console.log('tran ' + x0 + ',' + y0 + ' -> ' + d0  );
   d1[2] = sin1 * x1 - cos1 * y1;	// console.log('tran ' + x1 + ',' + y1 + ' -> ' + d1  );
return LP_cramer2( d0, d1 );
}

function LP_pie( ctx, diam, vals, colors, labels ) {
// normaliser les valeurs, en radian
var tot = 0;
for	( i in vals )
	tot += vals[i];
var k = 2 * Math.PI / tot;
for	( i in vals )
	vals[i] *= k;
// preparer layout
var radius = diam/2;
var x0 = radius;
var y0 = radius;
var offset = 2;
var da = ( offset / radius );
var a0 = -0.5 * Math.PI;	// mettre origine en haut
var a1, pic;
// tracer les parts de tarte
for	( i in vals )
	{
	if	( vals[i] > 0.0 )
		{
		a1 = a0 + vals[i];
		ctx.beginPath();
		ctx.arc( x0, y0, radius, a0+da, a1-da );
		pic = LP_peak( a0, a1, offset )
		ctx.lineTo( x0 + pic[0], y0 + pic[1] );
		ctx.closePath();
		ctx.fillStyle = colors[i];
		ctx.fill();
		ctx.stroke();	
		a0 = a1;
		}
	}
}

//ctx.font = "12px Arial"; ctx.fillStyle = "#F00";
//ctx.fillText("Hello little World", 10, 90);
