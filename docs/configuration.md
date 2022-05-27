# Configuration reference

```yaml
smart_sonata:
    sender:
        address: # Required - default sender e-mail address.
        name: # Optionnal - sender address name alias
    translate_email: # Optionnal default false - set it to true to enable translation template path based on locale
    emails: # If defined you must set at least one email
        - domain1.your.email.code # here are some email codes examples (for this case the first two will be group in the domain1 on the generated doc page)
        - domain1.another.email.code
        - domain2.your.email.code
```
