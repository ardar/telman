// JavaScript Document
ZoneContiner.prototype.Delete=function(divid)
{

	if(this.selectdivid==divid)this.selectdivid=null;
	var   d   =   document.getElementById(divid); 

	var   p 
	if   (p   =   d.parentNode) 
	{ 
		p.removeChild(d);
	}

	for(var i=0;i<this.divs.length;i++)
	{
		var id=this.divs[i];
		if(id==divid)
		{
			this.divs.splice(i,1);

			return;
		}
	}

}
ZoneContiner.prototype.SetOption=function(value)
{	
	if(value.width!=null)
	{
		this.maxW=value.width;
		this.minW=Math.floor(26*100/value.width+0.5);
		if(this.minW==0)this.minW=1;
		this.divobj.style.width=value.width+"px";
	}
	if(value.height!=null)
	{
		this.maxH=value.height;
		this.minH=Math.floor(26*100/value.height+0.5);
		if(this.minH==0)this.minH=1;
		this.divobj.style.height=value.height+"px";
	}
	if(value.canX!=null)
	this.canX=value.canX;
	if(value.callback!=null)
	this.callback=value.callback;
	if(value.bkimg!=null)
	{
		this.divobj.style.backgroundImage="url("+value.bkimg+")";
	}
	if(value.bgcolor!=null)
	{
		this.divobj.style.backgroundColor=value.bgcolor;
	}
	if(value.icon!=null)
	{
		var iconobj = window.document.getElementById('divicon_'+this.selectdivid);
		if(iconobj)
		{
			iconobj.src = value.icon;
		}
	}
	if(value.name!=null)
	{
		var obj = window.document.getElementById('divname_'+this.selectdivid);
		if(obj)
		{
			obj.innerHTML = value.name;
		}
	}
	this.divobj=window.document.getElementById(this.divName);
	
}
ZoneContiner.prototype.ToP=function(percentstring)
{
	percentstring = String(percentstring);
	var str=percentstring.replace("%","");
	return	Math.floor(parseInt(str)+0.5);

}
ZoneContiner.prototype.ShowTop=function(id)
{
	
	if(this.selectdivid!=null)
	{
		var selobj=window.document.getElementById(this.selectdivid);
		if(selobj!=null)
		selobj.style.zIndex="0";
	}
	
	var selobj=window.document.getElementById(id);
	if(selobj!=null)
	selobj.style.zIndex="99";
	
}
ZoneContiner.prototype.Select=function(id)
{
	//alert("select"+id);
	this.ShowTop(id);
	for(var i=0;i<this.selectlist.length;i++)
	{
		var selobj=window.document.getElementById("table_"+this.selectlist[i]);
		if(selobj!=null)
		{
			//selobj.style.backgroundImage="url(images/dragzone/divbk.png)";
			selobj.className = 'zonetable';
		}
	}
	this.selectlist.length=0;
	if(this.selectdivid!=null)
	{
		var selobj=window.document.getElementById("table_"+this.selectdivid);
		if(selobj!=null)
		//selobj.style.backgroundImage="url(images/dragzone/divbk.png)";
		selobj.className = 'zonetable';
	}
	
	var divobj=window.document.getElementById(id);
	if(divobj==null)
	{
		alert("divobj is null: "+id);
		return false;
	}
	this.selectdivid=id;
	var selobj=window.document.getElementById("table_"+this.selectdivid);
	if(selobj!=null)
	{
		selobj.className = 'zonetable_sel';
		//selobj.style.backgroundImage="url(images/dragzone/divselect.png)";
	}
	this.selectlist[this.selectlist.length]=id;	
	
	if(this.callback!=null)
	{
		//alert('callback from select');
		this.callback(divobj.id,this.GetTop(divobj.id),this.GetLeft(divobj.id),this.GetWidth(divobj.id),this.GetHeight(divobj.id));
	}
	//alert("selectend"+id);
	

}
ZoneContiner.prototype.IsX=function(div1left,div1top,div1width,div1height,div2left,div2top,div2width,div2height)
{
	var div1l=div1left;
	var div1t=div1top;
	var div1r=div1left+div1width;
	var div1b=div1top+div1height;
		var div2l=div2left;
	var div2t=div2top;
	var div2r=div2left+div2width;
	var div2b=div2top+div2height;
	
	if(((div1l>div2l&&div1l<div2r)||(div1r>div2l&&div1r<div2r)
		)&&(
									(div1t>div2t&&div1t<div2b)||(div1b>div2t&&div1b<div2b)
									))return true;
	var ptl={
		x:div1l,
		y:(div1t+div1b)/2
	};
	var ptt={
		x:(div1l+div1r)/2,
		y:div1t
	};
	var ptb={
		x:(div1l+div1r)/2,
		y:div1b
	};
	var ptr={
		x:div1r,
		y:(div1t+div1b)/2
	};
	var ptc={
		x:(div1l+div1r)/2,
		y:(div1t+div1b)/2
	};
	if(ptl.x>div2l&&ptl.x<div2r&&ptl.y>div2t&&ptl.y<div2b)return true;
	if(ptr.x>div2l&&ptr.x<div2r&&ptr.y>div2t&&ptr.y<div2b)return true;
	if(ptb.x>div2l&&ptb.x<div2r&&ptb.y>div2t&&ptb.y<div2b)return true;
	if(ptt.x>div2l&&ptt.x<div2r&&ptt.y>div2t&&ptt.y<div2b)return true;
	if(ptc.x>div2l&&ptc.x<div2r&&ptc.y>div2t&&ptc.y<div2b)return true;
	 div2l=div1left;
	 div2t=div1top;
	 div2r=div1left+div1width;
	 div2b=div1top+div1height;
	 div1l=div2left;
	 div1t=div2top;
	 div1r=div2left+div2width;
	 div1b=div2top+div2height;


		if(((div1l>div2l&&div1l<div2r)||(div1r>div2l&&div1r<div2r)
		)&&(
									(div1t>div2t&&div1t<div2b)||(div1b>div2t&&div1b<div2b)
									))return true;
	var ptl2={
		x:div1l,
		y:(div1t+div1b)/2
	};
	var ptt2={
		x:(div1l+div1r)/2,
		y:div1t
	};
	var ptb2={
		x:(div1l+div1r)/2,
		y:div1b
	};
	var ptr2={
		x:div1r,
		y:(div1t+div1b)/2
	};
	var ptc2={
		x:(div1l+div1r)/2,
		y:(div1t+div1b)/2
	};
	if(ptl2.x>div2l&&ptl2.x<div2r&&ptl2.y>div2t&&ptl2.y<div2b)return true;
	if(ptr2.x>div2l&&ptr2.x<div2r&&ptr2.y>div2t&&ptr2.y<div2b)return true;
	if(ptb2.x>div2l&&ptb2.x<div2r&&ptb2.y>div2t&&ptb2.y<div2b)return true;
	if(ptt2.x>div2l&&ptt2.x<div2r&&ptt2.y>div2t&&ptt2.y<div2b)return true;
	if(ptc2.x>div2l&&ptc2.x<div2r&&ptc2.y>div2t&&ptc2.y<div2b)return true;
	return false;
}
ZoneContiner.prototype.TryMove=function(divobj,leftpercent,toppercent,widthpercent,heightpercent)
{
	var oriCanX = this.canX;
	this.canX = false;
	var oldleft=this.ToP(divobj.style.left);
	var oldtop=this.ToP(divobj.style.top);
	var oldwidth=this.ToP(divobj.style.width);
	var oldheight=this.ToP(divobj.style.height);

	for(var i=oldleft;i!=leftpercent;)
	{
		if(oldleft<leftpercent)
			i++;
		else i--;
		if(!this.Move(divobj,i,oldtop,oldwidth,oldheight))
		{
			break;
		}	
	}
	oldleft=this.ToP(divobj.style.left);
	oldtop=this.ToP(divobj.style.top);
	oldwidth=this.ToP(divobj.style.width);
	oldheight=this.ToP(divobj.style.height);

	for(var i=oldtop;i!=toppercent;)
	{
		if(oldtop<toppercent)
		i++;
		else i--;
		if(!this.Move(divobj,oldleft,i,oldwidth,oldheight))break;
		
	}
	
	oldleft=this.ToP(divobj.style.left);
	oldtop=this.ToP(divobj.style.top);
	oldwidth=this.ToP(divobj.style.width);
	oldheight=this.ToP(divobj.style.height);
	for(var i=oldwidth;i!=widthpercent;)
	{
		if(oldwidth<widthpercent)
		i++;
		else i--;
		if(!this.Move(divobj,oldleft,oldtop,i,oldheight))break;

	}
	oldleft=this.ToP(divobj.style.left);
	oldtop=this.ToP(divobj.style.top);
	oldwidth=this.ToP(divobj.style.width);
	oldheight=this.ToP(divobj.style.height);
	
	for(var i=oldheight;i!=heightpercent;)
	{
	
		if(oldheight<heightpercent)
		i++;
		else i--;
		if(!this.Move(divobj,oldleft,oldtop,oldwidth,i))break;
	
	}
	this.canX = oriCanX;
}
ZoneContiner.prototype.Move=function(divobj,leftpercent,toppercent,widthpercent,heightpercent)
{
	//widthpercent=Math.floor(widthpercent);
	//heightpercent=Math.floor(heightpercent);
	//top=Math.floor(top);
	//left=Math.floor(left);

	if(toppercent<0)toppercent=0;
	if(leftpercent<0)leftpercent=0;
	if(toppercent>100)toppercent=100;
	if(leftpercent>100)leftpercent=100;
	if(widthpercent<this.minW)widthpercent=this.minW;
	if(heightpercent<this.minH)heightpercent=this.minH;
		
	if(widthpercent>100)widthpercent=100;
	if(heightpercent>100)heightpercent=100;
	
	if(leftpercent+widthpercent>100)
	{
		
		leftpercent=100-widthpercent;
	}
	
	if(toppercent+heightpercent>100)
	{
		toppercent=100-heightpercent;
	}
	if(toppercent<0)toppercent=0;
	if(leftpercent<0)lleftpercenteft=0;
	if(toppercent>100)toppercent=100;
	if(leftpercent>100)leftpercent=100;
	
	
	var ret = true;
	for(var i=0;i<this.divs.length&&!this.canX;i++)
	{
		var id=this.divs[i];
		if(id==divobj.id)continue;
		var obj=window.document.getElementById(id);
		if(this.IsX(this.ToP(obj.style.left),this.ToP(obj.style.top),this.ToP(obj.style.width),this.ToP(obj.style.height),
					leftpercent,toppercent,widthpercent,heightpercent
					))
		{
			//alert("fail in IsX");
			ret= false;
			break;
		}
	}
	if(ret)
	{
		divobj.style.top=toppercent+"%";
		divobj.style.left=leftpercent+"%";
		divobj.style.width=widthpercent+"%";
		divobj.style.height=heightpercent+"%";
	}

	
	//alert("callback in Move "+divobj.style.top+","+divobj.style.left+","+divobj.style.width+","+divobj.style.height+".");
	
	if(this.callback!=null)
	{

		this.callback(divobj.id,this.GetTop(divobj.id),this.GetLeft(divobj.id),this.GetWidth(divobj.id),this.GetHeight(divobj.id));
	}
	return ret;
}

ZoneContiner.prototype.GetTop=function(divid)
{
	var obj=window.document.getElementById(divid);
	return this.ToP(obj.style.top);
}
ZoneContiner.prototype.GetLeft=function(divid)
{
	var obj=window.document.getElementById(divid);
	return this.ToP(obj.style.left);
}
ZoneContiner.prototype.GetWidth=function(divid)
{
	var obj=window.document.getElementById(divid);
	return this.ToP(obj.style.width);
}
ZoneContiner.prototype.GetHeight=function(divid)
{
	var obj=window.document.getElementById(divid);
	return this.ToP(obj.style.height);
}

ZoneContiner.prototype.SetBkColor=function(divid,bgcolor)
{

	var divobj=window.document.getElementById(divid);
	if(bgcolor!=null)
	{
		divobj.style.backgroundColor=bgcolor;
	}
	

}
ZoneContiner.prototype.SetBkImg=function(divid,bgimg)
{
	var divobj=window.document.getElementById(divid);
	if(divobj !=null)
	{
		if(bgimg)
			divobj.style.backgroundImage="url("+bgimg+")";
		else
			divobj.style.backgroundImage="";
	}
}
ZoneContiner.prototype.SetTop=function(divid,value)
{

	var obj=window.document.getElementById(divid);
	return this.Move(obj,this.ToP(obj.style.left),this.ToP(value),this.ToP(obj.style.width),this.ToP(obj.style.height));

}
ZoneContiner.prototype.SetLeft=function(divid,value)
{
	var obj=window.document.getElementById(divid);
	return this.Move(obj,this.ToP(value),this.ToP(obj.style.top),this.ToP(obj.style.width),this.ToP(obj.style.height));

}
ZoneContiner.prototype.SetWidth=function(divid,value)
{
	var obj=window.document.getElementById(divid);
	return this.Move(obj,this.ToP(obj.style.left),this.ToP(obj.style.top),this.ToP(value),this.ToP(obj.style.height));

	
}
ZoneContiner.prototype.SetHeight=function(divid,value)
{
	var obj=window.document.getElementById(divid);
	return this.Move(obj,this.ToP(obj.style.left),this.ToP(obj.style.top),this.ToP(obj.style.width),this.ToP(value));
}
function ZoneContiner(objName,continer,width,height,canX,bgcolor,bgimg,callback)
{

	this.obj=objName;
	this.divName=continer;
	this.maxW=width;
	this.maxH=height;
	this.minW=Math.floor(26*100/width+0.5);
	if(this.minW==0)this.minW=1;
	
	this.minH=Math.floor(26*100/height+0.5);
	if(this.minH==0)this.minH=1;
	this.canX=canX;
	this.divobj=window.document.getElementById(continer);
	this.callback=callback;
	this.divobj.style.height=height+"px";
	this.divobj.style.width=width+"px";
	this.divobj.style.backgroundColor=bgcolor;
	this.divobj.style.backgroundImage="url("+bgimg+")";
	this.divobj.className = "container";
	//this.divobj.style.position="absolute";
	//this.divobj.style.border="2px solid black";
	
	this.divs=new Array;
	this.selectlist=new Array;
	this.selectdivid=null;
	this.nowevent=null;
	this.lbX=0;
	this.lbY=0;
	this.isShiftDown=false;
	var con=this;
		this.divobj.onmousemove=function(ev)
	{
		con.MouseMove(ev);
	}
	
	document.onkeydown=function(ev)
	{
		if(ev==null)ev=window.event;
		var value= ev.keyCode ; 
		if(value==16)con.isShiftDown=true;
	}

	document.onkeyup=function(ev)
	{
		if(ev==null)ev=window.event;
		var value= ev.keyCode ; 
		if(value==16)con.isShiftDown=false;
	}


	document.onmouseup=function(ev)
	{
		con.nowevent=null;
	
	}
	
	document.onmousedown=function(ev)
	{
		if(ev==null)ev=window.event;
		var mousept=con.getMousePoint(ev);
		mousept.x-=con.divobj.offsetLeft;
		mousept.y-=con.divobj.offsetTop;//鼠标在容器的位置
		var mX=Math.floor(mousept.x*100/con.divobj.offsetWidth+0.5);
		var mY=Math.floor(mousept.y*100/con.divobj.offsetHeight+0.5);
		con.lbX=mX;
		con.lbY=mY;
	}
	
}
ZoneContiner.prototype.MouseMove=function(ev)
{
	if(ev==null)ev=window.event;
	var seldiv;
	if(this.nowevent==null||this.selectdivid==null)
	{
		return;
	}
	//alert(this.selectdivid);
	
	seldiv=window.document.getElementById(this.selectdivid);
	if(seldiv==null)return;

	//alert(ev);
	var mousept=this.getMousePoint(ev);
	mousept.x-=this.divobj.offsetLeft;
	mousept.y-=this.divobj.offsetTop;//鼠标在容器的位置
	var mX=Math.floor(mousept.x*100/this.divobj.offsetWidth+0.5);
	var mY=Math.floor(mousept.y*100/this.divobj.offsetHeight+0.5);

	var divobj=seldiv;
	if(this.nowevent=="rc")
	{
		this.Move(divobj,this.ToP(divobj.style.left),this.ToP(divobj.style.top),mX-this.ToP(divobj.style.left),this.ToP(divobj.style.height));

	}else
	if(this.nowevent=="b")
	{
		this.Move(divobj,this.ToP(divobj.style.left),this.ToP(divobj.style.top),this.ToP(divobj.style.width),mY-this.ToP(divobj.style.top));

	}else
	if(this.nowevent=="rb")
	{
		this.Move(divobj,this.ToP(divobj.style.left),this.ToP(divobj.style.top),mX-this.ToP(divobj.style.left),mY-this.ToP(divobj.style.top));
	}else
	if(this.nowevent=="c")
	{
		
		var wayx=this.lbX-this.ToP(divobj.style.left);
		var wayy=this.lbY-this.ToP(divobj.style.top);
		if(mX-wayx!=this.ToP(divobj.style.left))
		{
			if(this.Move(divobj,mX-wayx,this.ToP(divobj.style.top),this.ToP(divobj.style.width),this.ToP(divobj.style.height)))//先移动x
			{
					this.lbX=mX;				
			}
		}
		if(mY-wayy!=this.ToP(divobj.style.top))
		{
			if(this.Move(divobj,this.ToP(divobj.style.left),mY-wayy,this.ToP(divobj.style.width),this.ToP(divobj.style.height)))
			{
				this.lbY=mY;
			}
		}

	}else
	if(this.nowevent=="t")
	{
		this.Move(divobj,this.ToP(divobj.style.left),mY,this.ToP(divobj.style.width),this.ToP(divobj.style.top)-mY+this.ToP(divobj.style.height));
	}
	else	if(this.nowevent=="lc")
	{
		this.Move(divobj,mX,this.ToP(divobj.style.top),this.ToP(divobj.style.left)-mX+this.ToP(divobj.style.width),this.ToP(divobj.style.height));
	}
		else	if(this.nowevent=="lt")
	{

			this.Move(divobj,mX,mY,this.ToP(divobj.style.left)-mX+this.ToP(divobj.style.width),this.ToP(divobj.style.top)-mY+this.ToP(divobj.style.height));
	}
		else	if(this.nowevent=="lb")
	{

			this.Move(divobj,mX,this.ToP(divobj.style.top),this.ToP(divobj.style.left)-mX+this.ToP(divobj.style.width),mY-this.ToP(divobj.style.top));
	}
	else	if(this.nowevent=="rt")
	{

		this.Move(divobj,this.ToP(divobj.style.left),mY,mX-this.ToP(divobj.style.left),this.ToP(divobj.style.top)-mY+this.ToP(divobj.style.height));
	}
	
}
ZoneContiner.prototype.AddZone=function(id,title,icon,leftpercent,toppercent,widthpercent,heightpercent,bgcolor,bgimg)
{
	//if(title.length>8)title=title.substr(0,8);
	var str="\
	<div class='zone' onmouseover=\"style.borderColor='red'\" onmouseout=\"style.borderColor=''\" id='"+id+
"'><table class='zonetable' cellspacing='0' cellpadding='0' id='table_"+id+"'>\
  <tr>\
    <td class='lt' onmousedown=\""+this.obj+".MouseDown('"+id+"','lt');\"></td>\
   <td class='t' onmousedown=\""+this.obj+".MouseDown('"+id+"','t');\"></td>\
    <td class='rt' onmousedown=\""+this.obj+".MouseDown('"+id+"','rt');\"></td>\
  </tr>\
  <tr>\
       <td class='lc' onmousedown=\""+this.obj+".MouseDown('"+id+"','lc');\"></td>\
   <td class='c' id='c_zone_"+id+"' onmousedown=\""+this.obj+".MouseDown('"+id+"','c');\">&nbsp;</td>\
    <td class='rc' onmousedown=\""+this.obj+".MouseDown('"+id+"','rc');\"></td>\
  </tr>\
  <tr>\
   <td class='lb' onmousedown=\""+this.obj+".MouseDown('"+id+"','lb');\"></td>\
   <td class='b' onmousedown=\""+this.obj+".MouseDown('"+id+"','b');\"></td>\
    <td class='rb' onmousedown=\""+this.obj+".MouseDown('"+id+"','rb');\"></td>\
  </tr>\
</table><div id='divtitle_"+id+"' class='titlediv'"+" onmousedown=\""+this.obj+".MouseDown('"+id+"','c');\""+">"+"<img src='"+icon+"' id='divicon_"+id+"'><span id='divname_"+id+"'>"+title+"</div></div>";

	this.divobj.innerHTML+=str;
	var divobj=window.document.getElementById(id);
	if(divobj==null)
	{
		return;
	}
	divobj.style.top="0%";
	divobj.style.left="0%";
	if(heightpercent>100)heightpercent=100;
	if(widthpercent>100)widthpercent=100;
	divobj.style.width=widthpercent+"%";
	divobj.style.height=heightpercent+"%";


	if(toppercent<0)toppercent=0;
	if(leftpercent<0)leftpercent=0;
	if(toppercent>100)toppercent=100;
	if(leftpercent>100)leftpercent=100;
	if(widthpercent<this.minW)widthpercent=this.minW;
	if(heightpercent<this.minH)heightpercent=this.minH;
	divobj.style.left = leftpercent+"%";
	divobj.style.top = toppercent+"%";
	divobj.style.width = widthpercent+"%";
	divobj.style.height = heightpercent+"%";

	//if(!this.Move(divobj,leftpercent,toppercent,widthpercent,heightpercent))
	{
	//	alert("move faild "+divobj.id+","+leftpercent+","+toppercent+","+widthpercent+","+heightpercent);
	}
	if(bgcolor!=null && bgcolor!='')
	{
		divobj.style.backgroundColor=bgcolor;
	}
	if(bgimg!=null && bgimg!='')
	{
		divobj.style.backgroundImage="url("+bgimg+")";
	}
	this.divs[this.divs.length]=id;
	//var tableobj=window.document.getElementById("table_"+id);
	//tableobj.style.backgroundImage="url(images/dragzone/divbk.png)";
	

}
ZoneContiner.prototype.MouseDown=function(id,type)
{
	
	this.ShowTop(id);
	var isShiftDown=this.isShiftDown;
	for(var i=0;i<this.selectlist.length&&!isShiftDown;i++)
	{
		var selobj=window.document.getElementById("table_"+this.selectlist[i]);
		if(selobj!=null)
		{
			//selobj.style.backgroundImage="url(images/dragzone/divbk.png)";
			selobj.className = 'zonetable';
		}
		var selobj=window.document.getElementById(""+this.selectlist[i]);
		if(selobj!=null)
		{
			//selobj.style.backgroundImage="url(images/dragzone/divbk.png)";
			selobj.className = 'zone';
		}
	}
	if(!isShiftDown)this.selectlist.length=0;
	if(this.selectdivid!=null)
	{
		var selobj=window.document.getElementById("table_"+this.selectdivid);
		if(selobj!=null)
		{
			//selobj.style.backgroundImage="url(images/dragzone/divbk.png)";
			selobj.className = 'zonetable';
		}
		var selobj=window.document.getElementById(""+this.selectlist[i]);
		if(selobj!=null)
		{
			//selobj.style.backgroundImage="url(images/dragzone/divbk.png)";
			//selobj.style.borderColor = '';
		}
	}
	
	var divobj=window.document.getElementById(id);
	this.selectdivid=id;
	var selobj=window.document.getElementById("table_"+this.selectdivid);
	if(selobj!=null)
	{
		selobj.className = 'zonetable_sel';
		//selobj.style.backgroundImage="url(images/dragzone/divselect.png)";
	}
	var selobj=window.document.getElementById(""+this.selectdivid);
	if(selobj!=null)
	{
		//selobj.style.borderColor = 'green';
		//selobj.style.backgroundImage="url(images/dragzone/divselect.png)";
	}
	this.nowevent=type;

	this.selectlist[this.selectlist.length]=id;	
	for(var i=0;i<this.selectlist.length&&isShiftDown;i++)
	{
		var selobj=window.document.getElementById("table_"+this.selectlist[i]);
		if(selobj!=null && selobj.id!=id)
		{
			selobj.className = 'zonetable_sel';
			//selobj.style.backgroundImage="url(images/dragzone/divselect.png)";
		}
	}
	if(this.callback!=null)
	{
		this.callback(divobj.id,this.GetTop(divobj.id),this.GetLeft(divobj.id),this.GetWidth(divobj.id),this.GetHeight(divobj.id));
	}

}

ZoneContiner.prototype.Fill=function(type,divlist)
{
	if(divlist==null)divlist=this.selectlist;
	if(divlist==null)return;
	var con=this;
	divlist=divlist.sort(function(a,b){
								  var diva=window.document.getElementById(a);
								  var divb=window.document.getElementById(b);
								  if(diva==null||divb==null)return true;
								  return   con.ToP(diva.style.left)<con.ToP(divb.style.left);
								  });
	divlist=divlist.sort(function(a,b){
									  var diva=window.document.getElementById(a);
									  var divb=window.document.getElementById(b);	
									  if(diva==null||divb==null)return true;
									  return   con.ToP(diva.style.top)<con.ToP(divb.style.top);
									  });

	for(var i=0;i<divlist.length;i++)
	{
		var divid=divlist[i];
		if(divid==null)continue;
		//alert(divid);
		if(type=="width")
		{
			var divobj=window.document.getElementById(divid);
			if(divobj!=null)
			this.TryMove(divobj,0,this.ToP(divobj.style.top),100,this.ToP(divobj.style.height));
		}
		if(type=="height")
		{
					var divobj=window.document.getElementById(divid);
			if(divobj!=null)
			this.TryMove(divobj,this.ToP(divobj.style.left),0,this.ToP(divobj.style.width),100);
		}
		if(type=="rect")
		{
			var divobj=window.document.getElementById(divid);
			if(divobj!=null)
			this.TryMove(divobj,0,0,100,100);
		}
	}
	
}
ZoneContiner.prototype.Side=function(type,divlist)
{
	if(divlist==null)divlist=this.selectlist;
	//alert(divlist.length);
	if(divlist==null||divlist.length<=0)return;
	var con=this;
	if(type=="left")//紧贴右侧
	{
			divlist=divlist.sort(function(a,b){
									  var diva=window.document.getElementById(a);
									  var divb=window.document.getElementById(b);
									  if(diva==null||divb==null)return true;
									  return   con.ToP(diva.style.left)>con.ToP(divb.style.left);
									  });
		var base=null;
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			//alert(divobj.id);
			if(base==null)
			{
				base=this.ToP(divobj.style.left)+this.ToP(divobj.style.width);
			}
			else
			{
				this.TryMove(divobj,base,this.ToP(divobj.style.top),this.ToP(divobj.style.width),this.ToP(divobj.style.height));
				base=this.ToP(divobj.style.left)+this.ToP(divobj.style.width);
			}
		}		
	}
	if(type=="right")//紧贴左侧
	{
			divlist=divlist.sort(function(a,b){
									  var diva=window.document.getElementById(a);
									  var divb=window.document.getElementById(b);
									  if(diva==null||divb==null)return true;
									  return   con.ToP(diva.style.left)+con.ToP(diva.style.width)<con.ToP(divb.style.left)+con.ToP(divb.style.width);
									  });
		var base=null;
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			if(base==null)
			{
				base=this.ToP(divobj.style.left);
			}
			else
			{
				this.TryMove(divobj,base-this.ToP(divobj.style.width),this.ToP(divobj.style.top),this.ToP(divobj.style.width),this.ToP(divobj.style.height));
				base=this.ToP(divobj.style.left);
			}
		}		
	}
	if(type=="top")//紧贴下面
	{
			divlist=divlist.sort(function(a,b){
									  var diva=window.document.getElementById(a);
									  var divb=window.document.getElementById(b);
									  if(diva==null||divb==null)return true;
									  return   con.ToP(diva.style.top)>con.ToP(divb.style.top);
									  });
		var base=null;
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			if(base==null)
			{
				base=this.ToP(divobj.style.top)+this.ToP(divobj.style.height);
			}
			else
			{
				this.TryMove(divobj,this.ToP(divobj.style.left),base,this.ToP(divobj.style.width),this.ToP(divobj.style.height));
				base=this.ToP(divobj.style.top)+this.ToP(divobj.style.height);
			}
		}		
	}
	if(type=="bottom")//紧贴下面
	{
			divlist=divlist.sort(function(a,b){
									  var diva=window.document.getElementById(a);
									  var divb=window.document.getElementById(b);
									  if(diva==null||divb==null)return true;
									  return   con.ToP(diva.style.top)+con.ToP(diva.style.height)<con.ToP(divb.style.top)+con.ToP(divb.style.height);
									  });
		var base=null;
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			if(base==null)
			{
				base=this.ToP(divobj.style.top);
			}
			else
			{
				this.TryMove(divobj,this.ToP(divobj.style.left),base-this.ToP(divobj.style.height),this.ToP(divobj.style.width),this.ToP(divobj.style.height));
				base=this.ToP(divobj.style.top);
			}
		}		
	}
}
ZoneContiner.prototype.Aglin=function(type,divlist)
{
	if(divlist==null)divlist=this.selectlist;
	if(divlist==null||divlist.length<=0)return;
	var con=this;

	if(type=="top")
	{
		var mintop=100;
		for(var i=0;i<divlist.length;i++)
		{
			var obj=window.document.getElementById(divlist[i]);
			if(obj==null)continue;
			if(this.ToP(obj.style.top)<mintop)mintop=this.ToP(obj.style.top);
		}
		if(mintop<0)mintop=0;
		divlist=divlist.sort(function(a,b){
									  var diva=window.document.getElementById(a);
									  var divb=window.document.getElementById(b);	
									  if(diva==null||divb==null)return true;
									  return   con.ToP(diva.style.top)>con.ToP(divb.style.top);
									  });
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			this.TryMove(divobj,this.ToP(divobj.style.left),mintop,this.ToP(divobj.style.width),this.ToP(divobj.style.height));
		}		
	}
	if(type=="left")
	{
		var minleft=100;
		for(var i=0;i<divlist.length;i++)
		{
			var obj=window.document.getElementById(divlist[i]);
			if(obj==null)continue;
			if(this.ToP(obj.style.left)<minleft)minleft=this.ToP(obj.style.left);
		}
		if(minleft<0)minleft=0;
		divlist=divlist.sort(function(a,b){

									  var diva=window.document.getElementById(a);
									  var divb=window.document.getElementById(b);
										if(diva==null||divb==null)return true;
									  return   con.ToP(diva.style.left)>con.ToP(divb.style.left);
									  })
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			this.TryMove(divobj,minleft,this.ToP(divobj.style.top),this.ToP(divobj.style.width),this.ToP(divobj.style.height));
		}		
	}
	if(type=="right")
	{
		var maxright=0;
		for(var i=0;i<divlist.length;i++)
		{
			var obj=window.document.getElementById(divlist[i]);
			if(obj==null)continue;
			if(this.ToP(obj.style.left)+this.ToP(obj.style.width)>maxright)maxright=this.ToP(obj.style.left)+this.ToP(obj.style.width);
		}
		if(maxright>100)maxright=100;
			divlist=divlist.sort(function(a,b){

									  var diva=window.document.getElementById(a);
									  var divb=window.document.getElementById(b);
										if(diva==null||divb==null)return true;
									  return  con.ToP(diva.style.left)+con.ToP(diva.style.width)<con.ToP(divb.style.left)+con.ToP(divb.style.width);
									  })
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			this.TryMove(divobj,maxright-this.ToP(divobj.style.width),this.ToP(divobj.style.top),this.ToP(divobj.style.width),this.ToP(divobj.style.height));
		}		
	}
	if(type=="bottom")
	{
		var maxbottom=0;
		for(var i=0;i<divlist.length;i++)
		{
			var obj=window.document.getElementById(divlist[i]);
			if(obj==null)continue;
			if(this.ToP(obj.style.top)+this.ToP(obj.style.height)>maxbottom)maxbottom=this.ToP(obj.style.top)+this.ToP(obj.style.height);
		}
		if(maxbottom>100)maxbottom=100;
		divlist=divlist.sort(function(a,b){
							  var diva=window.document.getElementById(a);
							  var divb=window.document.getElementById(b);
							  if(diva==null||divb==null)return true;
							  return   con.ToP(diva.style.top)+con.ToP(diva.style.height)<con.ToP(divb.style.top)+con.ToP(divb.style.height);
							  });
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			this.TryMove(divobj,this.ToP(divobj.style.left),maxbottom-this.ToP(divobj.style.height),this.ToP(divobj.style.width),this.ToP(divobj.style.height));
		}		
	}
	if(type=="xcenter")//竖向居中
	{
		var center=100;
		var minleft=100;
		var maxright=0;
		for(var i=0;i<divlist.length;i++)
		{
			var obj=window.document.getElementById(divlist[i]);
			if(obj==null)continue;
			if(this.ToP(obj.style.left)<minleft)minleft=this.ToP(obj.style.left);
			if(this.ToP(obj.style.left)+this.ToP(obj.style.width)>maxright)maxright=this.ToP(obj.style.left)+this.ToP(obj.style.width);
		}
		center=(minleft+maxright)/2;
		if(center<0||center>100)center=50;
		divlist=divlist.sort(function(a,b){
							  var diva=window.document.getElementById(a);
							  var divb=window.document.getElementById(b);	
							  if(diva==null||divb==null)return true;
							  return   con.ToP(diva.style.left)>con.ToP(divb.style.left);
							  });
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			this.TryMove(divobj,Math.floor(center-this.ToP(divobj.style.width)/2),this.ToP(divobj.style.top),this.ToP(divobj.style.width),this.ToP(divobj.style.height));
		}		
	}
	if(type=="ycenter")//横向居中
	{
		var center=100;
		var mintop=100;
		var maxbottom=0;
		for(var i=0;i<divlist.length;i++)
		{
			var obj=window.document.getElementById(divlist[i]);
			if(obj==null)continue;
			if(this.ToP(obj.style.top)<mintop)mintop=this.ToP(obj.style.top);
			if(this.ToP(obj.style.top)+this.ToP(obj.style.height)>maxbottom)maxbottom=this.ToP(obj.style.top)+this.ToP(obj.style.height);
		}
		center=(mintop+maxbottom)/2;
		if(center<0||center>100)center=50;
				divlist=divlist.sort(function(a,b){
							  var diva=window.document.getElementById(a);
							  var divb=window.document.getElementById(b);	
							  if(diva==null||divb==null)return true;
							  return   con.ToP(diva.style.top)>con.ToP(divb.style.top);
							  });
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			this.TryMove(divobj,this.ToP(divobj.style.left),Math.floor(center-this.ToP(divobj.style.height)/2),this.ToP(divobj.style.width),this.ToP(divobj.style.height));
		}		
	}
	if(type=="width")//平分宽度
	{
		var width=Math.floor(100/divlist.length);
		var end=100-width*divlist.length;
		divlist=divlist.sort(function(a,b){
								  var diva=window.document.getElementById(a);
								  var divb=window.document.getElementById(b);
								  if(diva==null||divb==null)return true;
								  return   con.ToP(diva.style.left)>con.ToP(divb.style.left);
								  });
		var tempX=this.canX;
		//this.canX=true;
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			this.Move(divobj,i*width,this.ToP(divobj.style.top),width,this.ToP(divobj.style.height));
		}
		if(end>0)
		{
			var divobj=window.document.getElementById(divlist[divlist.length-1]);
			if(divobj!=null)
			{
				this.Move(divobj,this.ToP(divobj.style.left),this.ToP(divobj.style.top),width+end,this.ToP(divobj.style.height));
			}
			
		}
		this.canX=tempX;
	}
	if(type=="height")//平分高度
	{
		var height=Math.floor(100/divlist.length);
		var end=100-height*divlist.length;
			divlist=divlist.sort(function(a,b){
									  var diva=window.document.getElementById(a);
									  var divb=window.document.getElementById(b);
									  if(diva==null||divb==null)return true;
									  return   con.ToP(diva.style.top)>con.ToP(divb.style.top);
									  });
		var tempX=this.canX;
		//this.canX=true;
		for(var i=0;i<divlist.length;i++)
		{
			var divobj=window.document.getElementById(divlist[i]);
			if(divobj==null)continue;
			this.Move(divobj,this.ToP(divobj.style.left),i*height,this.ToP(divobj.style.width),height);
		}
		if(end>0)
		{
			var divobj=window.document.getElementById(divlist[divlist.length-1]);
			if(divobj!=null)
			{
				this.Move(divobj,this.ToP(divobj.style.left),this.ToP(divobj.style.top),this.ToP(divobj.style.width),height+end);
			}
			
		}
		this.canX=tempX;
	}

}
ZoneContiner.prototype.ConverMouse=function(x,y)
{
	var point = {
		x:0,
		y:0
	};
 	
	// 如果浏览器支持 pageYOffset, 通过 pageXOffset 和 pageYOffset 获取页面和视窗之间的距离
	if(typeof window.pageYOffset != 'undefined') {
		point.x = window.pageXOffset;
		point.y = window.pageYOffset;
	}
	// 如果浏览器支持 compatMode, 并且指定了 DOCTYPE, 通过 documentElement 获取滚动距离作为页面和视窗间的距离
	// IE 中, 当页面指定 DOCTYPE, compatMode 的值是 CSS1Compat, 否则 compatMode 的值是 BackCompat
	else if(typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') {
		point.x = document.documentElement.scrollLeft;
		point.y = document.documentElement.scrollTop;
	}
	// 如果浏览器支持 document.body, 可以通过 document.body 来获取滚动高度
	else if(typeof document.body != 'undefined') {
		point.x = document.body.scrollLeft;
		point.y = document.body.scrollTop;
	}
 
	// 加上鼠标在视窗中的位置
	point.x +=x;
	point.y +=y;
 
	// 返回鼠标在视窗中的位置
	return point;
}
ZoneContiner.prototype.getMousePoint=function(ev) {
	// 定义鼠标在视窗中的位置
	var point = {
		x:0,
		y:0
	};
 
	// 如果浏览器支持 pageYOffset, 通过 pageXOffset 和 pageYOffset 获取页面和视窗之间的距离
	if(typeof window.pageYOffset != 'undefined') {
		point.x = window.pageXOffset;
		point.y = window.pageYOffset;
	}
	// 如果浏览器支持 compatMode, 并且指定了 DOCTYPE, 通过 documentElement 获取滚动距离作为页面和视窗间的距离
	// IE 中, 当页面指定 DOCTYPE, compatMode 的值是 CSS1Compat, 否则 compatMode 的值是 BackCompat
	else if(typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') {
		point.x = document.documentElement.scrollLeft;
		point.y = document.documentElement.scrollTop;
	}
	// 如果浏览器支持 document.body, 可以通过 document.body 来获取滚动高度
	else if(typeof document.body != 'undefined') {
		point.x = document.body.scrollLeft;
		point.y = document.body.scrollTop;
	}
 
	// 加上鼠标在视窗中的位置
	point.x += ev.clientX;
	point.y += ev.clientY;
 
	// 返回鼠标在视窗中的位置
	return point;
}
