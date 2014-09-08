# Security

Hero follows all of the best practices for web application security:

* Sanitizing and validating all POST and GET input data (never trust your input!)
* Auto-sanitizing all input for Cross Site Scripting attacks
* Escaping all database queries to nullify potentially malicious code
* Independent control panel and frontend user sessions
* Secure, encrypted cookies

Hero uses all of CodeIgniter's available security routines and best practices, [documented here](http://codeigniter.com/user_guide/general/security.html).