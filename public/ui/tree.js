// JavaScript Document
function Node(id,caption,isfloder,subnodelink,url,target,clickjs,dbclickjs,hover,icon,iconopen)
{
	this.id=id;
	if(caption==""||caption==null||caption=="null")this.caption="";
	else this.caption=caption;
	this.isfloder=isfloder;
	if(url==""||url==null)this.url=null;
	else this.url=url;
	this.target=target;
	if(clickjs==""||clickjs==null)this.clickjs=null;
	else this.clickjs=clickjs;
	if(subnodelink==""||subnodelink==null)this.subnodelink=null;
	else this.subnodelink=subnodelink;
	
	if(dbclickjs==""||dbclickjs==null)this.dbclickjs=null;
	else this.dbclickjs=dbclickjs;
	if(hover==""||hover==null||hover=="null")this.hover=null;
	else this.hover=hover;
	this.icon=icon;
	this.iconopen=iconopen;
	if(isfloder)
	{
			if(icon==""||icon==null)this.icon="images/dtree/folder.gif";
		if(iconopen==""||iconopen==null)this.iconopen="images/dtree/folderopen.gif";
	}
	else
	{
			if(icon==""||icon==null)this.icon="images/dtree/file.gif";
		if(iconopen==""||iconopen==null)this.iconopen="images/dtree/file.gif";
	}
	
	this.childnodes=new Array;
	this.isopen=false;
	this.level=0;
	this.parentnode=null;
	this.isloaded=false;
}
Node.prototype.Set=function(caption,isfloder,subnodelink,url,target,clickjs,dbclickjs,hover,icon,iconopen)
{
	if(caption==""||caption==null)this.caption="";
	else this.caption=caption;
	this.isfloder=isfloder;
	if(url==""||url==null)this.url=null;
	else this.url=url;
	this.target=target;
	if(clickjs==""||clickjs==null)this.clickjs=null;
	else this.clickjs=clickjs;
	if(subnodelink==""||subnodelink==null)this.subnodelink=null;
	else this.subnodelink=subnodelink;
	
	if(dbclickjs==""||dbclickjs==null)this.dbclickjs=null;
	else this.dbclickjs=dbclickjs;
	if(hover==""||hover==null||hover=="null")this.hover=null;
	else this.hover=hover;
	this.icon=icon;
	this.iconopen=iconopen;
	if(isfloder)
	{
			if(icon==""||icon==null)this.icon="images/dtree/folder.gif";
	if(iconopen==""||iconopen==null)this.iconopen="images/dtree/folderopen.gif";
	}
	else
	{
			if(icon==""||icon==null)this.icon="images/dtree/file.gif";
		if(iconopen==""||iconopen==null)this.iconopen="images/dtree/file.gif";
	}
	this.childnodes=null;
	this.childnodes=new Array;
	this.isloaded=false;
}

function Tree(objName,divID,rootSrc)
{
	
	this.divID=divID;
	this.obj=objName;
	var tree=this;
	/*var div=document.getElementById(this.divID);
	
	
	div.ondblclick=function()
		{
		
			if(tree.selectnode!=null)
			{
				
				eval(tree.selectnode.dbclickjs);
				tree.selectnode=null;
			}
	

		}*/
		
     $.get(rootSrc,{t:new Date()},function(data){
	//1.服务器设置的text/xml格式

					var nodes = data.selectNodes("/TreeNode"); //这里data直接就已经是xml对象了，可以直接使用xpath进行解析
					var id=nodes[0].getAttribute("id");
					var caption=nodes[0].getAttribute("text");		
					var subnodelink=nodes[0].getAttribute("subnodelink");
					var url=nodes[0].getAttribute("url");
					var target=nodes[0].getAttribute("target");
					var clickjs=nodes[0].getAttribute("clickjs");
					var dbclickjs=nodes[0].getAttribute("dbclickjs");
					var hover=nodes[0].getAttribute("hover");
					var icon=nodes[0].getAttribute("icon");
					var iconopen=nodes[0].getAttribute("iconopen");
					
					var isfloder;
					if(subnodelink==null|subnodelink=="")isfloder=false;
					else isfloder=true;
					
			
					nodes = data.selectNodes("/TreeNode/TreeNode");
					if(nodes==null||nodes.length<=0)isfloder=false;
					else isfloder=true;
					tree.root=new Node(id,caption,isfloder,subnodelink,url,target,clickjs,dbclickjs,hover,icon,iconopen);
					tree.XmlToNode(nodes,id);
					tree.root.isfloder=true;
					tree.root.isloader=true;
					tree.selectnode=tree.root;
					tree.root.isopen=true;
					tree.Show();
				});
}
Tree.prototype.AddbyId=function(parentid,id,caption,isfloder,subnodelink,url,target,clickjs,dbclickjs,hover,icon,iconopen)
{
	var pnode=this.FindNode(null,parentid);
	return this.Add(pnode,id,caption,isfloder,subnodelink,url,target,clickjs,dbclickjs,hover,icon,iconopen);
}
Tree.prototype.Add=function(parentnode,id,caption,isfloder,subnodelink,url,target,clickjs,dbclickjs,hover,icon,iconopen)
{
	var index=parentnode.childnodes.length;
	parentnode.childnodes[index]=new Node(id,caption,isfloder,subnodelink,url,target,clickjs,dbclickjs,hover,icon,iconopen);
	parentnode.childnodes[index].level=parentnode.level+1;
	parentnode.childnodes[index].parentnode=parentnode;
	
	return parentnode.childnodes[index];
}
Tree.prototype.Show=function()
{
	var div=document.getElementById(this.divID);
	var str='';

	str=str+this.build(this.root,this);
	div.innerHTML=str;
	
	return null;
}
Tree.prototype.select = function(id) 
{

	if(this.selectnode!=null)
	{
		var span=window.document.getElementById("span_"+this.selectnode.id);
		span.style.backgroundColor="";
	}
	this.selectnode=this.FindNode(null,id);
	if(this.selectnode==null){
	
		return;}
	this.selectnode.isopen=!this.selectnode.isopen;

	var span=window.document.getElementById("span_"+this.selectnode.id);

	span.style.backgroundColor="#d0f0f0";
	var div=window.document.getElementById("div_sub_"+this.selectnode.id);
	var icon=window.document.getElementById("icon_"+this.selectnode.id);
	var botimg=window.document.getElementById("headimg_bot_"+this.selectnode.id);
	var midimg=window.document.getElementById("headimg_mid_"+this.selectnode.id);
	if(this.selectnode.isopen&&this.selectnode.isfloder&&this.selectnode.childnodes.length>0)
	{
		div.style.display="";
	}else div.style.display="none";
	if(this.selectnode.isopen)
	{
		if(icon)icon.src=this.selectnode.iconopen;
		if(this.selectnode.isfloder)
		{		
			if(botimg)botimg.src="images/dtree/minusbottom.gif";
			if(midimg)midimg.src="images/dtree/minus.gif";
		}
	}else
	{
		if(icon)icon.src=this.selectnode.icon;
		if(this.selectnode.isfloder)
		{		
			if(botimg)botimg.src="images/dtree/plusbottom.gif";
			if(midimg)midimg.src="images/dtree/plus.gif";
		}
	}
	if(this.selectnode.isopen&&this.selectnode.isfloder&&this.selectnode.isloaded==false)
	{
		this.getChildren(this.selectnode);

	}

//	this.Show();
	
}
Tree.prototype.build=function(node,obj)
{
	var str="<div id='div_"+node.id+"' "
	str+=">";
	str+=this.NodeToHtml(node);
	str+="<div id='div_sub_"+node.id+"' ";
	if(!node.isopen)str+="style='display:none;'";
	else str+="style=''";
	str+=">";
	for(var i=0;i<node.childnodes.length;i++)
	{

		str+=obj.build(node.childnodes[i],obj);
				
	}
	str+="</div>";
	str+="</div>";

	return str;
}
Tree.prototype.NodeToHtml=function(node)
{

	var retstr=""
	retstr+=this.indent(node);

	var str="";
	str+="<span width='100%'  id='span_"+node.id+"' ";
	if(node.id==this.selectnode.id)str+="style='background-color:#d0f0f0';";

	str+="onclick=\"";
	if(node.clickjs!=null&&node.clickjs!="")
	{
		str+=Convertyinhao(node.clickjs);
		str+=";"
	}
	str+=this.obj+".select("+node.id+");"
	str+="\" "
	//
	if(node.dbclickjs!=null&&node.dbclickjs!="")
	{
		str+=" ondblclick=\""+Convertyinhao(node.dbclickjs)+"\" ";
	}

	if(node.hover!=null&&node.hover!="")
	{
		
		str+=" onmouseover=\"javascript:"+this.obj+".showhover(this,"+node.id+");\" onmouseout=\"javascript:"+this.obj+".hidehover();\"";

	}
	str+="><a ";
	if(node.url!=null&&node.hover!="")
	{
		str+=" href=\""+Convertyinhao(node.url)+"\" target=\""+node.target+"\" ";
	}
	str+=">";
	
	var parentnode=node.parentnode;
	if(node==this.root)
	{/*
		if(node.isopen)
		{
			str=str+"<img src='images/dtree/nolines_minus.gif'>";
		}
		else
		{
			str=str+"<img src='images/dtree/nolines_plus.gif'>";	
		}
		*/
	}
	else
	{
		if(parentnode!=null&&parentnode.childnodes.length!=0&&parentnode.childnodes[parentnode.childnodes.length-1]==node)
		{
			if(node.isfloder)
			{
				if(node.isopen)
				{
					str=str+"<img id=\"headimg_bot_"+node.id+"\" src=\"images/dtree/minusbottom.gif\"/> ";
				}
				else
				{
					str=str+"<img id=\"headimg_bot_"+node.id+"\" src=\"images/dtree/plusbottom.gif\"/> ";	
				}
			}else
			{
					str=str+"<img id=\"headimg_bot_"+node.id+"\"  src=\"images/dtree/joinbottom.gif\"/> ";	
			}
			
			
			
		}
		else
		{
			//选取中间过程图片图片
			if(node.isfloder)
			{
				if(node.isopen)
				{
					str=str+"<img id=\"headimg_mid_"+node.id+"\" src=\"images/dtree/minus.gif\"/> ";
				}
				else
				{
					str=str+"<img id=\"headimg_mid_"+node.id+"\" src=\"images/dtree/plus.gif\"/> ";	
				}
			}else
			{
					str=str+"<img  id=\"headimg_mid_"+node.id+"\" src=\"images/dtree/join.gif\"/> ";	
			}
			
		}
	}
	if(node.isopen)
	{
		str=str+"<span><img id=\"icon_"+node.id+"\" src=\""+node.iconopen+"\"/></span> ";
	}
	else
	{
		str=str+"<span><img id=\"icon_"+node.id+"\" src=\""+node.icon+"\" /></span> ";
	}
	
	str+=node.caption;
	str+="</a></span>"
	retstr+=str;


	return retstr;
}
Tree.prototype.showhover=function(obj,id)
{

	var div=window.document.getElementById("hoverdiv");
	var node=this.FindNode(null,id);
	if(div==null||node==null||node.hover==null)return;
	div.innerHTML=node.hover;
	var top=obj.offsetTop+obj.offsetHeight*2;
	var left=obj.offsetLeft+30;
	div.style.position="absolute";
	div.style.top=top;
	div.style.left=left;
	div.style.display="";
}
Tree.prototype.hidehover=function()
{
	var div=window.document.getElementById("hoverdiv");
		if(div==null)return;
	//window.document.title=div.scrollTop+" d "+div.offsetTop;
	div.style.display="none";
	
}
Tree.prototype.indent = function(node) {
	var str="";
	
	var parentnode=node.parentnode;
	if(parentnode==null)return str;
	var nownode=parentnode;
	parentnode=parentnode.parentnode;
	while(parentnode!=null&&parentnode.childnodes.length>0)
	{
		if(parentnode.childnodes[parentnode.childnodes.length-1]==nownode)
		{
			str="<img src='images/dtree/empty.gif'>"+str;//选取空白图片
		}
		else
		{
			str="<img src='images/dtree/line.gif'>"+str;//选取中间过程图片图片
		}
		parentnode=parentnode.parentnode;
		nownode=parentnode;
	}

	return str;

};

Tree.prototype.FindNode=function(node,id)
{

	var pnode=node;
	if(node==null)pnode=this.root;
	if(pnode.id==id)
	{
		
		return pnode;
	}
			
	for(var i=0;i<pnode.childnodes.length;i++)
	{
		var child=pnode.childnodes[i];
		if(child.id==id)return child;
		var retnode=this.FindNode(child,id);
		if(retnode!=null)return retnode;
	}
	return null;
}


function loadXML(flag,xml){
	
	
	var xmlDoc;
	//针对IE浏览器	
	if(window.ActiveXObject)
	{
		var aVersions = ["MSXML2.DOMDocument.6.0","MSXML2.DOMDocument.5.0","MSXML2.DOMDocument.4.0","MSXML2.DOMDocument.3.0","MSXML2.DOMDocument","Microsoft.XmlDom"];
		for (var i = 0; i < aVersions.length; i++) 
		{		
			try {	
			//建立xml对象
				xmlDoc = new ActiveXObject(aVersions[i]);				
				break;			
			} catch (oError) {}		
		}
	
		if(xmlDoc != null)
		{	
			//同步方式加载XML数据
			xmlDoc.async = false;
			//根据XML文档名称装载		
			if(flag == true)
			{	
				xmlDoc.load(xml);
			} else
			{			
				//根据表示XML文档的字符串装载
				xmlDoc.loadXML(xml);			
			}
			//返回XML文档的根元素节点。

			return xmlDoc.documentElement;
		}
	
	} else
	{	
	//针对非IE浏览器	
		if(document.implementation && document.implementation.createDocument)
		{	
		  /*	
		   第一个参数表示XML文档使用的namespace的URL地址
		   第二个参数表示要被建立的XML文档的根节点名称	
		   第三个参数是一个DOCTYPE类型对象，表示的是要建立的XML文档中DOCTYPE部分的定义，通常我们直接使用null	
		   这里我们要装载一个已有的XML文档，所以首先建立一个空文档，因此使用下面的方式	
		  */	
		  xmlDoc = document.implementation.createDocument("","",null);	
		  if(xmlDoc != null)
		  {			  
				//根据XML文档名称装载	
				if(flag == true)
				{	
				  //同步方式加载XML数据	
					xmlDoc.async = false;
					xmlDoc.load(xml);
				} else
				{	
				  //根据表示XML文档的字符串装载	
				  var oParser = new DOMParser();	
				  xmlDoc = oParser.parseFromString(xml,"text/xml");	
				}	
				//返回XML文档的根元素节点。	
				return xmlDoc.documentElement;	
		  }
	
		}
	
	}

  return null;

}
function Convertyinhao(str)
{
	
	return str.replace(/\"/g,"'");
}
Tree.prototype.XmlToNode=function(XmlNodes,parentid)
{
	if(XmlNodes==null)return;
	for(var i=0;i<XmlNodes.length;i++)
	{
		var id=XmlNodes[i].getAttribute("id");
	
		var caption=XmlNodes[i].getAttribute("text");
		
		var subnodelink=XmlNodes[i].getAttribute("subnodelink");
		var url=XmlNodes[i].getAttribute("url");
		var target=XmlNodes[i].getAttribute("target");
		var clickjs=XmlNodes[i].getAttribute("clickjs");
		var dbclickjs=XmlNodes[i].getAttribute("dbclickjs");
		var hover=XmlNodes[i].getAttribute("hover");
		
		var icon=XmlNodes[i].getAttribute("icon");
		var iconopen=XmlNodes[i].getAttribute("iconopen");
		var isfloder;
		if(subnodelink==null|subnodelink=="")isfloder=false;
		else isfloder=true;
		var children = XmlNodes[i].childNodes; //得到user节点的子节点集合
		//if(children.length>0)isfloder=true;

		this.AddbyId(parentid,id,caption,isfloder,subnodelink,url,target,clickjs,dbclickjs,hover,icon,iconopen);
		
		this.XmlToNode(children,id);
		continue;
	 }
}
Tree.prototype.getChildren=function(node){
 	if(node.subnodelink==""||node.subnodelink==null||node.isfloder==false||node.isloader==true)
	{
		return;
	}
	var tree=this;
	
     $.get(node.subnodelink,{t:new Date()},function(data){
	//1.服务器设置的text/xml格式
					
					var nodes = data.selectNodes("/TreeNode"); //这里data直接就已经是xml对象了，可以直接使用xpath进行解析
					//var id=nodes[0].getAttribute("id");
					var caption=nodes[0].getAttribute("text");		
					var subnodelink=nodes[0].getAttribute("subnodelink");
					var url=nodes[0].getAttribute("url");
					var target=nodes[0].getAttribute("target");
					var clickjs=nodes[0].getAttribute("clickjs");
					var dbclickjs=nodes[0].getAttribute("dbclickjs");
					var hover=nodes[0].getAttribute("hover");
					var icon=nodes[0].getAttribute("icon");
					var iconopen=nodes[0].getAttribute("iconopen");
					var isfloder;
					if(subnodelink==null|subnodelink=="")isfloder=false;
					else isfloder=true;

					//node.Set(caption,isfloder,subnodelink,url,target,clickjs,dbclickjs,hover,icon,iconopen);
					nodes = data.selectNodes("/TreeNode/TreeNode");
					tree.XmlToNode(nodes,node.id);
					node.isfloder=true;
					node.isloader=true;
					//tree.needreflesh=true;
						var selectnode=tree.selectnode;
						var str = '';
						var div=window.document.getElementById("div_sub_"+selectnode.id);
						
						for(var i=0;i<selectnode.childnodes.length;i++)
						{
							
							str+=tree.build(selectnode.childnodes[i],tree);
			
						}
						div.innerHTML=str;
						if(selectnode.isopen&&selectnode.isfloder&&selectnode.childnodes.length>0)
						{
							div.style.display="";
						}else div.style.display="none";
						
						
				});
}
