// const os = require("os");

// console.log(os.freemem());
// console.log(os.homedir());
// console.log(os.hostname());
// console.log(os.platform());
// console.log(os.totalmem());
// console.log(os.type());
// console.log(os.uptime());

// let http=require("http");
// http.createServer((req,res)=>{
//      res.write("hello hii");
//      res.end()
// }).listen(3000,()=>console.log("server start..."));


let express=require("express");
let app=express();
app.listen(3000,()=>console.log("server strat.."));
app.use(express.urlencoded({extended:true}));
app.use(express.text());
app.use(express.json());

app.get("/",(req,res)=>{
   res.send("hello")
})

app.get("/query",(req,res)=>{
    let a=req.query;
    res.send(a);

 })

 let array=[];
 app.get("/params/:name/:age",(req,res)=>{

         let data=req.params; 
     array.push(data)
         res.send(array);
 })

 app.post("/register",async(req,res)=>{
      let data=await req.body;
      res.send(data.name)
 })